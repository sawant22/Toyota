<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model;

class Media extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    protected const CACHE_TAG = 'media_set';
    
    /**
     * @var string
     */
    protected $_cacheTag = 'media_set';
    
    /**
     * @var string
     */
    protected $_eventPrefix = 'media_set';
    
    /**
     * Constructur function
     *
     * @param \Infosys\PriceAdjustment\Model\ResourceModel\Media::class
     */
    protected function _construct()
    {
        $this->_init(\Infosys\PriceAdjustment\Model\ResourceModel\Media::class);
    }
    
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    
    /**
     * Get default value
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
