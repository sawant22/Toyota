<?php

/**
 * @package     Infosys/CheckoutVIN
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CheckoutVIN\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
/**
 * Class to convert quote object field into order object field
 */
class SaveVinDetails implements ObserverInterface
{
    /**
     * Method to update sales order vin details
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $quote = $observer->getEvent()->getData('quote');
        if ($quote->getVinDetails()) {
            $order->setVinDetails($quote->getVinDetails());
        }
        return $this;
    }
}
