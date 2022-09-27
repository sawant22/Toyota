<?php
/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\CustomerCentral\Model\ResourceModel;

/**
 * Class CustomerCentralOrderQueue
 * @package Infosys\CustomerCentral\Model\ResourceModel
 */

class CustomerCentralOrderQueue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('customer_central_order_queue', 'queue_id');
    }
}
