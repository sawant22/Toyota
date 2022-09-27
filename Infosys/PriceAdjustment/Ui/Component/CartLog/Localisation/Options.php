<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Ui\Component\CartLog\Localisation;

use Magento\Backend\Model\Auth\Session as BackendSession;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $websiteCollectionFactory;
    /**
     * @var BackendSession
     */
    private $backendSession;
    
    /**
     * @param BackendSession $backendSession
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory
     */
    public function __construct(
        BackendSession $backendSession,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory
    ) {
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->backendSession = $backendSession;
    }
    
    /**
     * Get all options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $userdata = $this->backendSession->getUser();
        if ($userdata && !$userdata->getAllWebsite()) {
            $websiteIds = $userdata->getWebsiteIds();
            $websiteId = explode(',', $websiteIds)[0];

            $collection = $this->websiteCollectionFactory->create()
                        ->addFieldToFilter('website_id', ['eq' => $websiteId])
                         ->getData();
        } else {
            $collection = $this->websiteCollectionFactory->create()
                        ->getData();
        }
    
        $websiteCollection = [];

        foreach ($collection as $value) {
            $websiteCollection[] = ['value' => $value['website_id'], 'label' => __($value['name'])];
        }

        return $websiteCollection;
    }
}
