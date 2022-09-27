<?php

/**
 * @package     Infosys/OrderAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\OrderAttribute\Model\Resolver\Order;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class OrderItemAttribute implements ResolverInterface
{
    /**
     * Get order item attribute value
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return void
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $orderItem =  $value['model'];
        if ($orderItem) {
            return $orderItem->getData($field->getName());
        }
    }
}
