<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CustomerCentral\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Infosys\CustomerCentral\Model\CustomerCentral;
use Infosys\CustomerCentral\Helper\Data;
use Infosys\CustomerCentral\Logger\CustomerCentralLogger;
use Infosys\CustomerCentral\Model\CustomerCentralOrderQueueFactory;
use Magento\Sales\Model\OrderFactory;

/**
 * Event after order placement
 */
class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerCentral
     */
    protected $customerCentral;

    /**
     * @var CustomerCentralLogger
     */
    protected $CCLogger;
    /**
     * @var Data
     */
    protected $helper;

    /**
     *
     * @var CustomerCentralOrderQueueFactory
     */
    protected $saveCustomerCentralOrderQueueFactory;

    /**
     *
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Constructor  function
     *
     * @param LoggerInterface $logger
     * @param CustomerCentral $customerCentral
     * @param CustomerCentralLogger $CCLogger
     * @param CustomerCentralOrderQueueFactory $saveCustomerCentralOrderQueueFactory
     * @param Data $helper
     * @param OrderFactory $orderFactory
     * \
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerCentral $customerCentral,
        CustomerCentralLogger $CCLogger,
        Data $helper,
        CustomerCentralOrderQueueFactory $saveCustomerCentralOrderQueueFactory,
        OrderFactory $orderFactory
    ) {
        $this->logger = $logger;
        $this->customerCentral = $customerCentral;
        $this->CCLogger = $CCLogger;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory->create();
        $this->customerCentralOrderQueue = $saveCustomerCentralOrderQueueFactory->create();
    }
    /**
     * Sync on order place after
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $order->save();
            $orderId = $order->getId();
            
            $retryCount = 0;
            $this->customerCentralOrderQueue->setData('order_id', $orderId)
                ->setData('retry_count', $retryCount)
                ->setData('api_status', "Pending");
            $this->customerCentralOrderQueue->save();
        } catch (\Exception $e) {
            $this->CCLogger->error($e->getMessage());
        }
    }
}
