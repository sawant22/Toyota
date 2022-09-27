<?php
/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\Import\Vehicle;

use Infosys\Vehicle\Model\ResourceModel\Vehicle\CollectionFactory;

/**
 * Vehicle collection
 */
class VehicleProcessor
{
    /**
     * @var \Infosys\Vehicle\Model\ResourceModel\Vehicle\CollectionFactory
     */
    protected $VehicleFactory;

    /**
     * @var array
     */
    protected $oldVehicles;

    /**
     * @param \Infosys\Vehicle\Model\ResourceModel\Vehicle\CollectionFactory $VehicleFactory
     */
    public function __construct(
        CollectionFactory $VehicleFactory
    ) {
        $this->vehicleFactory = $VehicleFactory;
    }
    
    /**
     * Get old vehicles array.
     *
     * @return array
     */
    public function getVehicles()
    {
        $oldVehicles = [];

        $collection = $this->vehicleFactory->create();
        $items = $collection->getItems();
        foreach ($items as $item) {
            $key = $item->getModelYear().'-'.$item->getModelCode();
            $oldVehicles[] = $key;
        }
        return $oldVehicles;
    }
}
