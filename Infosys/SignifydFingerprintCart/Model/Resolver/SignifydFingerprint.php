<?php

/**
 * @package     Infosys/SignifydFingerprintCart
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SignifydFingerprintCart\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Signifyd\Connect\CustomerData\Fingerprint;

/**
 * Resolver class for the `Cart` mutation. Adds an signifyd fingerprint to cart.
 */
class SignifydFingerprint implements ResolverInterface
{

    /**
     * @var Fingerprint
     */
    private $fingerprint;

    /**
     * Generate Fingerprint for guest and customer cart.
     *
     * @param Fingerprint $fingerprint
     */
    public function __construct(
        Fingerprint $fingerprint
    ) {
        $this->fingerprint = $fingerprint;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return $this->fingerprint->getDeviceFingerprint();
    }
}
