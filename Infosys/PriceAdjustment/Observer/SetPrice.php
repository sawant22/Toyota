<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\PriceAdjustment\Observer;

use Infosys\PriceAdjustment\Helper\Data;
use \Magento\Framework\Event\ObserverInterface;

class SetPrice implements ObserverInterface
{
    /**
     * @var Data
     */
    public $helperData;

    /**
     * Contruct function
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }
    /**
     * Execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $eventName = $event->getName();
        $item = $event->getData('quote_item');
        $item = ($item->getParentItem() ? $item->getParentItem() : $item);
        $productOrignalPrice = $item->getPrice();
        $quote = $item->getQuote();
        $productId = $item->getProductId();
        $mediasetPrice = $this->helperData->getProductMediaSetPrice($productId, $item->getPrice());
        $item->setCustomPrice($mediasetPrice);
        $item->setOriginalCustomPrice($mediasetPrice);
        $item->getProduct()->setIsSuperMode(true);
    }
}
