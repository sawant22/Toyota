<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Infosys\Vehicle\Api\Data\VehicleProductMappingInterface;

class VehicleProductMapping extends AbstractExtensibleModel implements VehicleProductMappingInterface
{
    /**
     * Initialize Resource Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\VehicleProductMapping::class);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getProductId()
    {
        return parent::getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
    /**
     * @inheritDoc
     */
    public function getVehicleId()
    {
        return parent::getData(self::VEHICLE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setVehicleId($vehicleId)
    {
        return $this->setData(self::VEHICLE_ID, $vehicleId);
    }
}
