<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Observer;

class OrderData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Add Order data during import
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderData = $observer->getData('update_data');
        $order = $observer->getData('order');
        if (isset($orderData['order_reference'])) {
            $order->setOrderReference($orderData['order_reference']);
        }

        foreach ($order->getAllItems() as $orderItem) {
            if (isset($orderData['items']) && !empty($orderData['items'])) {
                foreach ($orderData['items'] as $itemRecord) {
                    if (isset($itemRecord['sku']) &&
                        strtolower($orderItem->getSku()) == strtolower($itemRecord['sku'])) {
                        if (isset($itemRecord['direct_fulfillment_status']) &&  ($orderItem->getDirectFulfillmentStatus() != 'SHIPPED')) {
                            $orderItem->setDirectFulfillmentStatus($itemRecord['direct_fulfillment_status']);
                        }
                        if (isset($itemRecord['direct_fulfillment_response'])) {
                            $orderItem->setDirectFulfillmentResponse($itemRecord['direct_fulfillment_response']);
                        }
                    }
                    $orderItem->save();
                }
            }
        }
        $order->save();
    }
}
