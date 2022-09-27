<?php

/**
 * @package     Infosys/PaymentWebsiteAssociation
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\PaymentWebsiteAssociation\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use StripeIntegration\Payments\Model\StripeCustomerFactory;

class StripeSecret implements ResolverInterface
{
    /**
     * @var StripeCustomerFactory
     */
    protected $StripeCustomerFactory;

    /**
     * Constuctor function
     * @param \StripeIntegration\Payments\Model\StripeCustomerFactory $stripeCustomerFactory
     */
    public function __construct(StripeCustomerFactory $stripeCustomerFactory)
    {
        $this->stripeCustomerFactory = $stripeCustomerFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $data = $this->stripeCustomerFactory->create()->load($context->getUserId(), 'customer_id')->getData();
        return $data['stripe_id'];
    }
}
