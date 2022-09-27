<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\ResourceModel\Vehicle;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Infosys\Vehicle\Model\ResourceModel\Vehicle as ResourceModelVehicle;
use Infosys\Vehicle\Model\Vehicle as ModelVehicle;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ModelVehicle::class,
            ResourceModelVehicle::class
        );
    }
}
