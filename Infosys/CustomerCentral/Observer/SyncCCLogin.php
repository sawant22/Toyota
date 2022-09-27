<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerCentral\Observer;

use Infosys\CustomerCentral\Model\CustomerCentral;

class SyncCCLogin implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var CustomerCentral
     */
    protected $customerCentral;
    /**
     * Constructor function
     *
     * @param CustomerCentral $customerCentral
     */
    public function __construct(CustomerCentral $customerCentral)
    {
        $this->customerCentral = $customerCentral;
    }
    /**
     * Sync customer data on login
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        if ($customer && !$customer->getCustomAttribute('customer_central_id')) {
            $this->customerCentral->syncCustomerOnUpdate($customer);
        }
    }
}
