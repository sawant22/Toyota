<?php

/**
 * @package     Infosys/Vehicle
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\ResourceModel\VehicleFitsQueue;

use Infosys\Vehicle\Model\ResourceModel\VehicleFitsQueue as ResourceModelVehicleFitsQueue;
use Infosys\Vehicle\Model\VehicleFitsQueue as ModelVehicleFitsQueue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ModelVehicleFitsQueue::class, ResourceModelVehicleFitsQueue::class);
    }
}
