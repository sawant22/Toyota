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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Infosys\CustomerCentral\Api\CustomerCentralInterface;

/**
 * Create customer account resolver
 */
class SyncCustomerCentral implements ResolverInterface
{
    /**

     * @var CustomerCentralInterface
     */
    private $customerCentral;
    /**
     * Constructor function
     *
     * @param CustomerCentralInterface $customerCentral
     */
    public function __construct(
        CustomerCentralInterface $customerCentral
    ) {
        $this->customerCentral = $customerCentral;
    }

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
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        if (!isset($args['input']['email']) || empty($args['input']['email'])) {
            throw new GraphQlInputException(__('"email" value should be specified'));
        }
        if (!isset($args['input']['first_name']) || empty($args['input']['first_name'])) {
            throw new GraphQlInputException(__('"first_name" value should be specified'));
        }
        if (!isset($args['input']['last_name']) || empty($args['input']['last_name'])) {
            throw new GraphQlInputException(__('"last_name" value should be specified'));
        }
        $customerData = new \Magento\Framework\DataObject();
        $customerData->setData($args['input']);
        $customerCentral = $this->customerCentral->syncGuestCustomerInCheckout($customerData);
        return ['createCustomerCentralProfile' => ['customer_central_id' => $customerCentral['customerCentralId']]];
    }
}
