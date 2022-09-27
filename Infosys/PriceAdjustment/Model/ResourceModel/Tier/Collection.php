<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model\ResourceModel\Tier;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'tier_price_tier_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'tier_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Infosys\PriceAdjustment\Model\Tier::class,
            \Infosys\PriceAdjustment\Model\ResourceModel\Tier::class
        );
    }
}
