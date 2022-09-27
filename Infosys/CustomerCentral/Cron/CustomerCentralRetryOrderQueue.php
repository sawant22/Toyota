<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerCentral\Cron;

use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Infosys\CustomerCentral\Helper\Data;
use Infosys\CustomerCentral\Model\CustomerCentral;
use Infosys\CustomerCentral\Logger\CustomerCentralLogger;
use Infosys\CustomerCentral\Model\ResourceModel\CustomerCentralOrderQueue\CollectionFactory;
use Infosys\CustomerCentral\Model\CustomerCentralOrderQueueFactory;

class CustomerCentralRetryOrderQueue
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
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_date;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var CollectionFactory
     */
    protected $ccOrderQueueCollectionFactory;
    /**
     *
     * @var CustomerCentralOrderQueueFactory
     */
    protected $saveCustomerCentralOrderQueueFactory;

    /**
     * Constructor function
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param CustomerCentral $customerCentral
     * @param CustomerCentralLogger $CCLogger
     * @param Data $helper
     * @param CollectionFactory $ccOrderQueueCollectionFactory
     * @param CustomerCentralOrderQueueFactory $saveCustomerCentralOrderQueueFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        CustomerCentral $customerCentral,
        CustomerCentralLogger $CCLogger,
        Data $helper,
        CollectionFactory $ccOrderQueueCollectionFactory,
        CustomerCentralOrderQueueFactory $saveCustomerCentralOrderQueueFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->customerCentral = $customerCentral;
        $this->CCLogger = $CCLogger;
        $this->helper = $helper;
        $this->ccOrderQueueCollectionFactory = $ccOrderQueueCollectionFactory->create();
        $this->customerCentralOrderQueue = $saveCustomerCentralOrderQueueFactory->create();
    }
    
    /**
     * Order Sync Retry Execute function
     *
     * @return $this
     */
    public function execute()
    {
        
        if ($this->helper->isLogEnabled()) {
            $this->CCLogger->info('Customer Central Sync retry order cron time');
        }
        
        $status = 'Retry';
        $retryCount = $this->helper->getRetryCount();

        $orders = $this->ccOrderQueueCollectionFactory
            ->addFieldToSelect(['order_id','retry_count'])
            ->addFieldToFilter('retry_count', ['lteq'=>$retryCount])
            ->addFieldToFilter('api_status', ['eq'=>$status]);
        
        foreach ($orders as $orderQueue) {
            $orderId = $orderQueue->getData('order_id');
            $order = $this->orderRepository->get($orderId);
            $response = $this->orderSync($order, $orderId);
            if($response != ''){
                $order->addCommentToStatusHistory($response);
                $this->orderRepository->save($order);
            }
        }

        return $this;
    }

    /**
     * Order sync with customer central
     * @param $order
     * @param $orderId
     * @return string
     */
    public function orderSync($order, $orderId)
    {
        try {
            $response = $this->customerCentral->syncCustomerOnOrderPlace($order);
            $orderQueue = $this->customerCentralOrderQueue->load($orderId, 'order_id');
            $retryCount = $orderQueue->getData('retry_count');
            $message = '';

            if (isset($response['customerId']) && $response['syncError'] == '') {
                if ($this->helper->isLogEnabled()) {
                    $this->CCLogger->info('CustomerCentralId-' . $response['customerId']);
                }

                $orderQueue->setData('api_status', "Success")
                    ->setData('messages', "Retry order sync cron - Order sync successfully done.")
                    ->save();
                
                if ($this->helper->isLogEnabled()) {
                    $this->CCLogger->info('Retry order sync cron - Order sync successfully done.');
                }
                
                $message = "Customer Central Notice: Order sync successfully done.";
                
            } else {
                $retryCount=$retryCount+1;

                $orderQueue->setData('api_status', "Retry")
                    ->setData('retry_count', $retryCount)
                    ->setData('messages', $response['syncError'])
                    ->save();
                
                if ($this->helper->isLogEnabled()) {
                    $this->CCLogger->info('Retry order sync cron - '. $response['syncError']);
                }

                $message = "Customer Central Notice: Order sync failed, details saved to logs.";

            }
            return $message; 
            
        } catch (\Exception $e) {
            $this->CCLogger->error($e->getMessage());
        }
    }
}
