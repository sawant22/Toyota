<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CustomerCentral\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Create customer account resolver
 */
class CustomerPhoneNumber implements ResolverInterface
{

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (isset($value['model'])) {
            $cutsomer = $value['model'];
            if (
                $cutsomer->getCustomAttribute('phone_number') &&
                $cutsomer->getCustomAttribute('phone_number')->getValue()
            ) {
                return $cutsomer->getCustomAttribute('phone_number')->getValue();
            }
        }
    }
}
