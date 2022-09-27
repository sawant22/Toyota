<?php

/**
 * @package     Infosys/OrderAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\OrderAttribute\Plugin\Model\Quote;

class ToOrderItem
{
    /**
     * Overriding the method to copy quote item value to order item
     *
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return void
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setVinNumber($item->getVinNumber());
        $orderItem->setVehicleName($item->getVehicleName());
        $orderItem->setFitmentNotice($item->getFitmentNotice());
        $orderItem->setFitmentStatus($item->getFitmentStatus());
        return $orderItem;
    }
}
