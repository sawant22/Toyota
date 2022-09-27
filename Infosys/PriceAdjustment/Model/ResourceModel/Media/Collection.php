<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model\ResourceModel\Media;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'media_set_media_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'media_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Infosys\PriceAdjustment\Model\Media::class,
            \Infosys\PriceAdjustment\Model\ResourceModel\Media::class
        );
    }
}
