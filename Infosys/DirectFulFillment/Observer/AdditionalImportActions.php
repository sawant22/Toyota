<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Observer;

use \Xtento\TrackingImport\Model\Processor\Mapping\AbstractMapping;

class AdditionalImportActions extends AbstractMapping implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Add Action during import
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $additionalAction = [
            'order_update_fee' => [
                'label' => __('Fee update'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => '',
                'tooltip' => __(
                    'If enabled, the order update service fee and freight recovery.'
                )
            ],
            'order_shipment_invoice_action' => [
                'label' => __('Import no shipment/invoice without DF Status as "SHIPPED"'),
                'default_values' => $this->getDefaultValues('yesno'),
                'default_value' => '',
                'tooltip' => __(
                    'If enabled, the order item will not be shipped/invoiced without DF status as "SHIPPED".'
                )
            ],
        ];
        $importActions = $observer->getData('importActions');
        $importActions = array_merge($importActions, $additionalAction);
        $observer->setData('importActions', $importActions);
    }
}
