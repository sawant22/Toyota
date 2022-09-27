<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CustomerCentral\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Store\Model\Store;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Additional button for Customer Central Sync in customer edit page
 */
class CustomerCentralSyncButton implements ButtonProviderInterface
{

    protected Registry $registry;

    protected Context $context;

    protected CustomerRepositoryInterface $customerRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->buttonList = $context->getButtonList();
        $this->authorization = $context->getAuthorization();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
        $this->storeManager = $context->getStoreManager();
        $this->customerRepository = $customerRepository;
    }

    /**
     * Customer central manual sync button data
     * 
     * @return array
     */
    public function getButtonData() : array
    {
        $data = [];
        if (!$this->getCustomerId()) {
            return $data;
        }

        $customerWebsite = $this->customerRepository->getById($this->getCustomerId())->getWebsiteId();
        if ($this->authorization->isAllowed('Infosys_CustomerCentral::customer_sync')
            && $this->storeManager->getStore(Store::ADMIN_CODE)->getWebsiteId() != $customerWebsite
        ) {
            $data =  [
                'label' => __('Sync To Customer Central'),
                'class' => 'primary',
                'on_click' => sprintf("location.href = '%s';", $this->getCustomerCentralSyncUrl()),
                'sort_order' => 90,
            ];
        }
        return $data;
    }

    /**
     * Get customer central sync URL
     * 
     * @return string
     */
    public function getCustomerCentralSyncUrl() : string
    {
        return $this->urlBuilder->getUrl('customer_sync/manualsync/index', ['customer_id' => $this->getCustomerId()]);
    }

    /**
     * Return the customer Id.
     * 
     * @return int
     */
    public function getCustomerId() : int
    {
        $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        if($customerId == null){
            $customerId = 0;
        }
        return $customerId;
    }
}
