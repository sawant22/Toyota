<?php
/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\CustomerCentral\Model\ResourceModel\CustomerCentralOrderQueue;

/**
 * Class Collection
 * @package Infosys\CustomerCentral\Model\ResourceModel\CustomerCentralOrderQueue
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(\Infosys\CustomerCentral\Model\CustomerCentralOrderQueue::class, \Infosys\CustomerCentral\Model\ResourceModel\CustomerCentralOrderQueue::class);
    }
}
