<?php

/**
 * @package     Infosys/OrderAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\OrderAttribute\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AddProductsToCart
{
    /**
     * Overriding the method to add custom fields value to quote item
     *
     * @param \Magento\QuoteGraphQl\Model\Resolver\AddProductsToCart $subject
     * @param array $result
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return void
     */
    public function afterResolve(
        \Magento\QuoteGraphQl\Model\Resolver\AddProductsToCart $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (isset($result['cart'])) {
            $quote =  $result['cart']['model'];
            $items = $quote->getAllVisibleItems();
            $cartItemsData = $args['cartItems'];
            /** @var QuoteItem $cartItem */
            foreach ($items as $item) {
                foreach ($cartItemsData as $requestItem) {
                    if (isset($requestItem['vin_number']) && $requestItem['sku'] == $item->getSku()) {
                        $item->setVinNumber($requestItem['vin_number']);
                    }
                    if (isset($requestItem['vehicle_name']) && $requestItem['sku'] == $item->getSku()) {
                        $item->setVehicleName($requestItem['vehicle_name']);
                    }
                    if (isset($requestItem['fitment_notice']) && $requestItem['sku'] == $item->getSku()) {
                        $item->setFitmentNotice($requestItem['fitment_notice']);
                    }
                    if (isset($requestItem['fitment_status']) && $requestItem['sku'] == $item->getSku()) {
                        $item->setFitmentStatus($requestItem['fitment_status']);
                    }
                    $item->save();
                }
            }
        }
        return $result;
    }
}
