<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model\Config\Source;

use Infosys\PriceAdjustment\Model\MediaFactory;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

class MediaSetOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    
    /**
     * @var MediaFactory
     */
    public $mediaFactory;
    
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $ProductAttributeRepositoryInterface;

    /**
     * @param MediaFactory $mediaFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductAttributeRepositoryInterface $ProductAttributeRepositoryInterface
     */
    public function __construct(
        MediaFactory $mediaFactory,
        StoreManagerInterface $storeManager,
        ProductAttributeRepositoryInterface $ProductAttributeRepositoryInterface
    ) {
        $this->mediaFactory = $mediaFactory;
        $this->storeManager = $storeManager;
        $this->ProductAttributeRepositoryInterface = $ProductAttributeRepositoryInterface;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $mediaDataQuery = $this->mediaFactory->create()->getCollection()
            ->addFieldToFilter('website', $websiteId);
        $mediaData = $mediaDataQuery->getData();
        $mediaSetOptions = $this->ProductAttributeRepositoryInterface->get('tier_price_set')->getOptions();
        $this->_options = [];
        $mediaSetOptionArray = [];
        foreach ($mediaSetOptions as $mediaSetOption) {
            $mediaSetOptionArray[$mediaSetOption->getValue()] = $mediaSetOption->getLabel();
        }
        
        foreach ($mediaData as $data) {
            $this->_options[] = ['label' => $mediaSetOptionArray[$data['media_set_selector']],
            'value' => $data['entity_id']];
        }
        return $this->_options;
    }
}
