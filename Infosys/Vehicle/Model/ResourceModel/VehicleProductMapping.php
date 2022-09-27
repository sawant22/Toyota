<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Infosys\Vehicle\Api\Data\VehicleProductMappingInterface;

class VehicleProductMapping extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(VehicleProductMappingInterface::VEHICLE_PRODUCT_MAPPING_TABLE, VehicleProductMappingInterface::ID);
    }
}
