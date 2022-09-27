<?php
/**
 * @package Infosys/ProductSaleable
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\ProductSaleable\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CatalogImportExport\Model\Import\Product\StockProcessor;
use Magento\Framework\Indexer\IndexerRegistry;
use Infosys\ProductSaleable\Logger\ProductLogger;

/**
 * Saves stock status data from a product to the Stock Item
 *
 */
class ProductSaleable implements ObserverInterface
{
    private StockRegistryInterface $stockRegistry;

    private StockProcessor $stockProcessor;

    private IndexerRegistry $indexerRegistry;

    private ProductLogger $logger;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @param StockProcessor $stockProcessor
     * @param IndexerRegistry $indexerRegistry
     * @param ProductLogger $logger
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        StockProcessor $stockProcessor,
        IndexerRegistry $indexerRegistry,
        ProductLogger $logger
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->stockProcessor = $stockProcessor;
        $this->indexerRegistry = $indexerRegistry;
        $this->_logger = $logger;
    }

    /**
     * Saving product stock status data
     *
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        try {
            /** @var Product $product */
            $product = $observer->getEvent()->getProduct();
            if ($product->getTypeId() != 'configurable') {
                $sku = $product->getSku();
                $extendedAttributes = $product->getExtensionAttributes();
                $stockItem = $extendedAttributes->getStockItem();
                
                //Global manage stock is disable. so need to enable/disable before update the stock status
                if ($product->getSaleable() == 'Y') {
                    $stockItem->setUseConfigManageStock(true);
                    $stockItem->setManageStock(false);
                    $stockItem->setIsInStock(1);
                } else {
                    $stockItem->setUseConfigManageStock(false);
                    $stockItem->setManageStock(true);
                    $stockItem->setIsInStock(0);
                }
                
                $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return $e->getMessage();
        }
    }
}
