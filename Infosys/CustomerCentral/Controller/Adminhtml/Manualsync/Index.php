<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CustomerCentral\Controller\Adminhtml\Manualsync;

use Magento\Framework\Controller\ResultFactory;
use Infosys\CustomerCentral\Model\CustomerCentral;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;
    /**
     * @var CustomerCentral
     */
    protected $customerCentral;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Constuctor function
     *
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CustomerCentral $customerCentral
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerCentral $customerCentral,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->customerCentral = $customerCentral;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }
    /**
     * Manual sync of all customers and their orders
     *
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $customerId = (int)$this->getRequest()->getParam('customer_id');

        if (!$customerId) {
            $resultRedirect->setPath('customer/index');
            return $resultRedirect;
        }

        $customer = $this->customerRepositoryInterface->getById($customerId);
        $response = $this->customerCentral->manualCustomerSync($customer);

        if(isset($response['error'])){
            foreach($response['error'] as $error){
                $this->messageManager->addErrorMessage($error);
            }
        }
        else{
            if(isset($response['warning'])){
                foreach($response['warning'] as $warning){
                    if(isset($warning['errorDescription'])){
                        $this->messageManager->addWarningMessage($warning['errorDescription']);
                    }
                    else{
                        $this->messageManager->addWarningMessage($warning);
                    }                    
                }
            }
            $this->messageManager->addSuccessMessage('Customer sync done successfully');

        }        
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed() : bool
    {
        return $this->_authorization->isAllowed('Infosys_CustomerCentral::customer_sync');
    }
}
