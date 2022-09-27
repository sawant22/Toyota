<?php
/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\DirectFulFillment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Infosys\DirectFulFillment\Helper\DirectFulFillment;

class OrderPlacebefore implements ObserverInterface
{
    
    /**
     * @var Infosys\DirectFulFillment\Helper\DirectFulFillment
     */
    protected $directFulfillment;

    /**
     * Constructor function
     *
     * @param DirectFulFillment $directFulfillment
     */
    public function __construct(
        DirectFulFillment $directFulfillment
    ) {
        $this->directFulfillment = $directFulfillment;
    }

    /**
     * Observer execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->directFulfillment->checkOrder($order);
    }
}
