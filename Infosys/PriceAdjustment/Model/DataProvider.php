<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Infosys\PriceAdjustment\Model\ResourceModel\Media\CollectionFactory;
use Magento\Backend\Model\Auth\Session;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;
    /**
     * @var Session
     */
    private $authSession;
    
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Session $authSession
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Session $authSession,
        array $meta = [],
        array $data = []
    ) {
        $this->authSession = $authSession;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
    
    /**
     * Get all options
     *
     * @return array
     */
    public function getCollection()
    {
        return $this->collection;
    }
    
    /**
     * Get all options
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $userdata = $this->authSession->getUser();
            if ($userdata && !$userdata->getAllWebsite()) {
                $websiteIds = $userdata->getWebsiteIds();
                $websiteId = explode(',', $websiteIds)[0];
                $this->getCollection()->addFieldToFilter('website', $websiteId);
            } else {
                $this->getCollection();
            }
        }
        return $this->getCollection()->toArray();
    }
}
