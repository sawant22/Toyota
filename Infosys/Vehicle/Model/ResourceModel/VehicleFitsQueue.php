<?php

/**
 * @package     Infosys/Vehicle
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class VehicleFitsQueue extends AbstractDb
{
    /**
     * Constructor function
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }
    /**
     * Initialize table data
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vehicle_fits_queue', 'entity_id');
    }
}
