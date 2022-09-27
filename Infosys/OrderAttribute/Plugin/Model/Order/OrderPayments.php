<?php

/**
 * @package     Infosys/OrderAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\OrderAttribute\Plugin\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Payment\Model\CcConfig;

class OrderPayments
{
    /**
     * @var CcConfig
     */
    protected $ccConfig;
    /**
     * Constructor function
     *
     * @param CcConfig $ccConfig
     */
    public function __construct(CcConfig $ccConfig)
    {
        $this->ccConfig = $ccConfig;
    }
    /**
     * Overriding the method to include cards details
     *
     * @param \Magento\SalesGraphQl\Model\Order\OrderPayments $subject
     * @param array $result
     * @param OrderInterface $orderModel
     * @return array
     */
    public function afterGetOrderPaymentMethod(
        \Magento\SalesGraphQl\Model\Order\OrderPayments $subject,
        $result,
        OrderInterface $orderModel
    ) {
        $orderPayment = $orderModel->getPayment();
        $cardTypes = $this->ccConfig->getCcAvailableTypes();
        $paymentData = current($result);
        $paymentData['cc_type'] = isset($cardTypes[$orderPayment->getCcType()]) ?
            $cardTypes[$orderPayment->getCcType()] : $orderPayment->getCcType();
        $paymentData['cc_exp_year'] = $orderPayment->getCcExpYear();
        $paymentData['cc_last_4'] = $orderPayment->getCcLast4();
        $paymentData['cc_exp_month'] = $orderPayment->getCcExpMonth();
        $result = [$paymentData];
        return $result;
    }
}
