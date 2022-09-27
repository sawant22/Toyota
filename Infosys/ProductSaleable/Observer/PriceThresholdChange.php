<?php

/**
 * @package   Infosys/ProductSaleable
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\ProductSaleable\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Infosys\ProductSaleable\Model\ThresholdPriceQueueFactory;

/**
 * Class to update product status after price change
 */
class PriceThresholdChange implements ObserverInterface
{
    
    protected ThresholdPriceQueueFactory $thresholdPriceQueueFactory;

    /**
     * Constructor function
     *
     * @param ThresholdPriceQueueFactory $thresholdPriceQueueFactory
     */
    public function __construct(
        ThresholdPriceQueueFactory $thresholdPriceQueueFactory
    ) {
        $this->thresholdPriceQueueFactory = $thresholdPriceQueueFactory;
    }

    /**
     * Method to update product status based on threshold price
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $priceQueue = $this->thresholdPriceQueueFactory->create();
        $priceQueue->setData('threshold_price_flag', 1);
        $priceQueue->save();
        return $this;
    }
}
