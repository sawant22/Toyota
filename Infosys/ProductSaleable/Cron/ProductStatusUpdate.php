<?php

/**
 * @package Infosys/ProductSaleable
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\ProductSaleable\Cron;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Infosys\ProductSaleable\Helper\Data;
use Infosys\ProductSaleable\Model\ThresholdPriceQueueFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Infosys\ProductSaleable\Logger\ProductLogger;

/**
 * Class to update product status based on threshold price through cron job
 */
class ProductStatusUpdate
{
    protected ProductLogger $logger;

    protected Json $json;

    protected ThresholdPriceQueueFactory $thresholdPriceQueueFactory;

    protected Data $helper;

    protected Action $action;

    protected Product $product;

    protected CollectionFactory $productCollectionFactory;

    /**
     * Constructor function
     *
     * @param ThresholdPriceQueueFactory $thresholdPriceQueueFactory
     * @param Json $json
     * @param ProductLogger $logger
     * @param Data $helper
     * @param Action $action
     * @param Product $product
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        ThresholdPriceQueueFactory $thresholdPriceQueueFactory,
        Json $json,
        ProductLogger $logger,
        Data $helper,
        Action $action,
        Product $product,
        CollectionFactory $productCollectionFactory
    ) {
        $this->thresholdPriceQueueFactory = $thresholdPriceQueueFactory;
        $this->json = $json;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->action = $action;
        $this->product = $product;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Method to update product status based on threshold price through cron job
     */
    public function execute()
    {
        $priceQueue = $this->thresholdPriceQueueFactory->create();
        $priceQueueItem = $priceQueue->getCollection()->getFirstItem();
        if (!empty($priceQueueItem) && $priceQueueItem->getThresholdPriceFlag() == 1) {
            $threshold_price = $this->helper->getThresholdPrice();
            $this->logger->info("Threshold price: " . $threshold_price);
            if ($threshold_price) {
                //get previously disabled products
                $disabledProducts = $this->productCollectionFactory->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('status', Status::STATUS_DISABLED)
                    ->getColumnValues('entity_id');
                $this->logger->info("Disabled Products: " . json_encode($disabledProducts));

                //get lessthan current threshold products
                $lessThanThresholdPriceProducts = $this->productCollectionFactory->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('price', ['lteq' => $threshold_price])
                    ->addAttributeToFilter('status', Status::STATUS_ENABLED)
                    ->getColumnValues('entity_id');
                $this->logger->info("Less than Threshold Price Products: " . json_encode($lessThanThresholdPriceProducts));

                $productIds = array_merge($disabledProducts, $lessThanThresholdPriceProducts);
                if ($productIds) {
                    foreach ($productIds as $productId) {
                        $product = $this->product->load($productId);
                        $stores = $product->getStoreIds();
                        array_unshift($stores, '0');
                        if ($product->getPrice() > $threshold_price) {
                            $updateAttributes['status'] = Status::STATUS_ENABLED;
                            foreach ($stores as $storeId) {
                                $this->action->updateAttributes([$product->getId()], $updateAttributes, $storeId);
                            }
                        } else {
                            $updateAttributes['status'] = Status::STATUS_DISABLED;
                            foreach ($stores as $storeId) {
                                $this->action->updateAttributes([$product->getId()], $updateAttributes, $storeId);
                            }
                        }
                    }
                }
            }
        }
        $priceQueue->load($priceQueueItem->getEntityId())->delete();
    }
}
