<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Observer;

class AdditionalImportFields implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Add Additional data to import
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $additional = $observer->getData('additional');
        $newFields = [
            'order_reference' => [
                'label' => __('Order Reference'),
            ],
            'service_fee' => [
                'label' => __('Service Fee'),
            ],
            'freight_recovery' => [
                'label' => __('Freight Recovery'),
                'group' => 'items'
            ],
            'direct_fulfillment_status' => [
                'label' => __('Direct Fulfillment Status'),
                'group' => 'items'
            ],
            'shipped_part_number' => [
                'label' => __('Shipped Part Number'),
                'group' => 'items'
            ],
            'direct_fulfillment_response' => [
                'label' => __('Direct Fulfillment Response'),
                'group' => 'items'
            ]
        ];
        $additional->setFields($newFields);
    }
}
