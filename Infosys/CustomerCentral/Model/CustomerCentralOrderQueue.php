<?php
/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerCentral\Model;

/**
 * Class CustomerCentralOrderQueue
 * @package Infosys\CustomerCentral\Model
 */
class CustomerCentralOrderQueue extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(\Infosys\CustomerCentral\Model\ResourceModel\CustomerCentralOrderQueue::class);
    }
}
