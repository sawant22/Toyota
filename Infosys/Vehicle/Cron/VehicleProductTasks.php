<?php

/**
 * @package   Infosys/Vehicle
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Vehicle\Cron;

use Infosys\Vehicle\Logger\VehicleLogger;
use Infosys\Vehicle\Model\Scheduler;

//Run vehicle and product import tasks
class VehicleProductTasks
{
    /**
     * @var VehicleLogger
     */
    protected $logger;

    /**
     * @var Scheduler
     */
    protected $Scheduler;

    /**
     * @var \Infosys\Vehicle\Helper\Data
     */
    protected $helperData;

    /**
     * Constuctor function
     *
     * @param Scheduler $Scheduler
     * @param VehicleLogger $logger
     * @param \Infosys\Vehicle\Helper\Data $helperData
     */
    public function __construct(
        Scheduler $Scheduler,
        VehicleLogger $logger,
        \Infosys\Vehicle\Helper\Data $helperData
    ) {
        $this->Scheduler = $Scheduler;
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * Execute function
     */
    public function execute()
    {
        $cron_status = $this->helperData->getConfig('epc_config/cron_settings/enable_tasks');
        if ($cron_status) {
            $this->Scheduler->scheduledTasks();
        } else {
            $this->logger->info('Cron disabled for scheduled tasks.');
        }
    }
}
