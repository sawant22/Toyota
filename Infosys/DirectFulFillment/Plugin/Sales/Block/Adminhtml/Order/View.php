<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright � 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View
{
    /**
     * Before plugin
     *
     * @param OrderView $subject
     * @return void
     */
    public function beforeSetLayout(OrderView $subject)
    {
        $order = $subject->getOrder();
        if ($order->canShip()
            && !$order->getForcedShipmentWithInvoice()
            && $order->getDirectFulfillmentStatus()
            && !$this->checkAlreadySent($order)
        ) {
            $subject->addButton(
                'order_custom_button',
                [
                    'label' => __('Direct Fulfillment'),
                    'class' => __('custom-button'),
                    'id' => 'order-view-custom-button',
                    'onclick' => 'setLocation(\'' . $subject->getUrl('direct/fulfillment/index') . '\')'
                ]
            );
        }
    }

    /**
     * Check Order submitted to DDOA
     *
     * @param mixed $order
     * @return mixed
     */
    public function checkAlreadySent($order)
    {
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getDealerDirectFulfillmentStatus()==1) {
                return true;
            }
        }
        return false;
    }
}
