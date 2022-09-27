<?php

/**
 * @package     Infosys/CustomerWebsiteOrders
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CustomerWebsiteOrders\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\SalesGraphQl\Model\Order\OrderAddress;
use Magento\SalesGraphQl\Model\Order\OrderPayments;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\OrderFactory;

/**
 * Orders data resolver
 */
class SuccessOrders implements ResolverInterface
{
    /**
     * @var OrderAddress
     */
    private $orderAddress;

    /**
     * @var OrderPayments
     */
    private $orderPayments;

    /**
     * @var CollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepositoryInterface;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * Constructor function
     *
     * @param OrderAddress $orderAddress
     * @param OrderPayments $orderPayments
     * @param CollectionFactoryInterface $collectionFactory
     * @param OrderRepositoryInterface $orderRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        OrderAddress $orderAddress,
        OrderPayments $orderPayments,
        CollectionFactoryInterface $collectionFactory,
        OrderRepositoryInterface $orderRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderFactory $orderFactory
    ) {
        $this->orderAddress = $orderAddress;
        $this->orderPayments = $orderPayments;
        $this->collectionFactory = $collectionFactory;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Resolver to get Guest orders
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return void
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return [
            'items' => $this->getOrderData($args['orderId'])
        ];
    }

    /**
     * Finds the order object corresponding to the passed cart hash.
     *
     * @param string $orderId
     * @throws GraphQlNoSuchEntityException
     * @return array
     */
    private function getOrderData(string $orderId)
    {
        if (!$orderId) {
            throw new GraphQlNoSuchEntityException(
                __(
                    'Could not find an order associated with input ID "%order_id"',
                    ['order_id' => $orderId]
                )
            );
        }

        $ordersArray = [];
        $orderModel = $this->orderFactory->create();
        $order = $orderModel->loadByIncrementId($orderId);
        if ($order->getId()) {
            $ordersArray[] = [
                'created_at' => $order->getCreatedAt(),
                'grand_total' => $order->getGrandTotal(),
                'id' => base64_encode($order->getEntityId()),
                'increment_id' => $order->getIncrementId(),
                'number' => $order->getIncrementId(),
                'order_date' => $order->getCreatedAt(),
                'order_number' => $order->getIncrementId(),
                'status' => $order->getStatusLabel(),
                'shipping_method' => $order->getShippingDescription(),
                'shipping_address' => $this->orderAddress->getOrderShippingAddress($order),
                'billing_address' => $this->orderAddress->getOrderBillingAddress($order),
                'payment_methods' => $this->orderPayments->getOrderPaymentMethod($order),
                'model' => $order,
                'email' => $order->getCustomerEmail()
            ];
        }
        return $ordersArray;
    }
}
