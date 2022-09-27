<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Vehicle\Cron;

use Infosys\Vehicle\Model\Import\ScheduledVehicleProductImport;

class VehicleProductImport
{

    /**
     * @var ScheduledVehicleProductImport
     */
    protected $scheduledVehicleProductImport;
    /**
     * Constuctor function
     *
     * @param ScheduledVehicleProductImport $scheduledVehicleProductImport
     */
    public function __construct(
        ScheduledVehicleProductImport $scheduledVehicleProductImport
    ) {
        $this->scheduledVehicleProductImport = $scheduledVehicleProductImport;
    }

    /**
     * Execute function
     */
    public function execute()
    {
        $this->scheduledVehicleProductImport->runScheduledImport();
    }
}
