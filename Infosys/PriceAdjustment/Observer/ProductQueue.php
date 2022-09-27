<?php

/**
 * @package Infosys/PriceAdjustment
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\PriceAdjustment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Infosys\PriceAdjustment\Model\TierQueueFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Infosys\PriceAdjustment\Logger\PriceCalculationLogger;
use Infosys\PriceAdjustment\Model\Config\Configuration;
use Magento\Framework\App\ResourceConnection;
use Infosys\PriceAdjustment\Publisher\TierPriceImport as Publisher;
use Magento\Framework\Serialize\Serializer\Json;
use Infosys\PriceAdjustment\Helper\Data;
use Magento\Catalog\Model\ProductFactory;

/**
 *  Class to publish data to rabbitmq while importing
 */

class ProductQueue implements ObserverInterface
{
    private TierQueueFactory $tierQueueFactory;
    
    protected ProductFactory $product;

    protected PriceCalculationLogger $logger;
    
    private ProductRepositoryInterface $productRepository;

    protected Configuration $dealerPriceConfig;

    protected ResourceConnection $resource;
    
    protected Data $data;
     
    public Json $serializer;
     
    private Publisher $publisher;

    /**
     * Construct
     *
     * @param TierQueueFactory $tierQueueFactory
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param PriceCalculationLogger $logger
     * @param ProductRepositoryInterface $productRepository
     * @param Configuration $dealerPriceConfig
     * @param ResourceConnection $resource
     * @param Data $data
     * @param Json $serializer
     * @param Publisher $publisher
     */
    public function __construct(
        TierQueueFactory $tierQueueFactory,
        \Magento\Catalog\Model\ProductFactory $product,
        PriceCalculationLogger $logger,
        ProductRepositoryInterface $productRepository,
        Configuration $dealerPriceConfig,
        ResourceConnection $resource,
        Data $data,
        Json $serializer,
        Publisher $publisher
    ) {
        $this->tierQueueFactory = $tierQueueFactory;
        $this->product = $product;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->dealerPriceConfig = $dealerPriceConfig;
        $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->data = $data;
    }

    /**
     * Execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {

        //Disable tier price import during product import
        if (!$this->dealerPriceConfig->enableTierPriceImportDuringImport()) {
            $this->logger->info("Tier Price Import During Product Import Is Disabled");
            return false;
        }
        $bunch = $observer->getEvent()->getBunch();
        $tierQueueData = [];
        foreach ($bunch as $rowNum => $rowData) {
            $websiteIds = $rowData['product_website_ids'];
            $tierMediaSet = [];
            if (!empty($websiteIds)) {
                if ($this->data->isRabbitMQEnabled()) {
                    $tierMediaSet['website'] = $websiteIds;
                    $tierMediaSet['sku'] = $rowData['sku'];
                    $tierMediaSet = $this->serializer->serialize($tierMediaSet);
                    $this->publisher->publish($tierMediaSet);
                } else {
                    $tierQueue = $this->tierQueueFactory->create();
                    $tierData = $tierQueue->getCollection()
                    ->addFieldToFilter('sku', $rowData['sku'])
                    ->getFirstItem()->getData();
                    try {
                        $product = $this->productRepository->get($rowData['sku']);
                        $ressource = $product->getResource();
                        $optionId = '';
                        $price = $rowData['price'];
                        $isAttributeExist = $ressource->getAttribute('tier_price_set');
                        $optionLabel = $rowData['tier_price_set'];
                        if ($isAttributeExist && $isAttributeExist->usesSource()) {
                            $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
                        }
                        //Insert into tier_queue table when there is no record for SKU during import
                        if (empty($tierData)) {
                            $tierQueueData[] = [
                            'sku' => $rowData['sku'],
                            'website' => $websiteIds,
                            'tier_price_set' => "",
                            'special_price_update_status' => "N",
                            'old_product_price' => $rowData['price'],
                            'old_tierprice_id' => $optionId
                            ];
                        } else {
                            // update tier_queue table when tier price set or product price is changed during import
                            if ($tierData['old_tierprice_id'] != $optionId || $tierData['old_product_price'] != $price) {
                                $tierQueueData[] = [
                                'sku' => $rowData['sku'],
                                'website' => $websiteIds,
                                'tier_price_set' => "",
                                'special_price_update_status' => "N",
                                'old_product_price' => $rowData['price'],
                                'old_tierprice_id' => $optionId
                                ];
                            }
                        }
                    } catch (\Exception $exception) {
                        $this->logger->error('tier price queue table insert error' . $exception->getMessage());
                    }
                }
            }
        }
        if (count($tierQueueData) > 0) {
            $validColumnNames = [
                'sku',
                'website',
                'tier_price_set',
                'special_price_update_status',
                'old_product_price',
                'old_tierprice_id'
            ];
            $this->_connection->insertOnDuplicate("tier_queue", $tierQueueData, $validColumnNames);
        }
    }
}
