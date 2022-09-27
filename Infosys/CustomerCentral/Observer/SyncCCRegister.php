<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerCentral\Observer;

use Infosys\CustomerCentral\Model\CustomerCentral;

class SyncCCRegister implements \Magento\Framework\Event\ObserverInterface
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
     * Sync Customer data on updating or creating customer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerData = $observer->getEvent()->getCustomerDataObject();
        $preCustomerData = $observer->getEvent()->getOrigCustomerDataObject();
        if ($preCustomerData) {
            if (($customerData->getFirstname() == $preCustomerData->getFirstname()) &&
                ($customerData->getLastname() == $preCustomerData->getLastname()) &&
                ($customerData->getMiddlename() == $preCustomerData->getMiddlename()) &&
                ($customerData->getEmail() == $preCustomerData->getEmail()) &&
                ($customerData->getCustomAttribute('phone_number') == $preCustomerData->getCustomAttribute('phone_number'))

            ) {
                return;
            }
        }

        $this->customerCentral->syncCustomerOnUpdate($customerData);
    }
}
