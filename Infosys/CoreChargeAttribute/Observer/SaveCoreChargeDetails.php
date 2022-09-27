<?php

/**
 * @package     Infosys/CoreChargeAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CoreChargeAttribute\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
/**
 * Class to convert quote object field into order object field
 */
class SaveCoreChargeDetails implements ObserverInterface
{
    /**
     * Method to update sales order core_charge details
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $quote = $observer->getEvent()->getData('quote');
        if ($quote->getCoreChargeDetails()) {
            $order->setCoreChargeDetails($quote->getCoreChargeDetails());
        }
        if ($quote->getTotalCoreCharge()) {
            $order->setTotalCoreCharge($quote->getTotalCoreCharge());
        }
        if ($quote->getPartNumber()) {
            $order->setPartNumber($quote->getPartNumber());
        }
        return $this;
    }
}
