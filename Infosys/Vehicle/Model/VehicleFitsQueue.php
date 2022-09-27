<?php

/**
 * @package     Infosys/Vehicle
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model;

use Magento\Framework\Model\AbstractModel;

class VehicleFitsQueue extends AbstractModel
{
    /**
     * Initialize Resource Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\VehicleFitsQueue::class);
    }
}
