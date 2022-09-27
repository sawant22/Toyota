<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model\ResourceModel\TierQueue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'tier_queue_tier_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'tier_queue_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Infosys\PriceAdjustment\Model\TierQueue::class,
            \Infosys\PriceAdjustment\Model\ResourceModel\TierQueue::class
        );
    }
}
