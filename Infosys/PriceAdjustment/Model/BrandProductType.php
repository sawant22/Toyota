<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare (strict_types = 1);

namespace Infosys\PriceAdjustment\Model;

/**
 * Class for media set model
 */
class BrandProductType extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    protected const CACHE_TAG = 'brand_product_type_media_set';
    
    /**
     * @var string
     */
    protected string $cacheTag = 'brand_product_type_media_set';
    
    /**
     * @var string
     */
    protected string $eventPrefix = 'brand_product_type_media_set';
    
    /**
     * Constructur function
     *
     * @param \Infosys\PriceAdjustment\Model\ResourceModel\BrandProductType::class
     */
    protected function _construct()
    {
        $this->_init(\Infosys\PriceAdjustment\Model\ResourceModel\BrandProductType::class);
    }
    
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    
    /**
     * Get default value
     *
     * @return array
     */
    public function getDefaultValues(): array
    {
        $values = [];

        return $values;
    }
}
