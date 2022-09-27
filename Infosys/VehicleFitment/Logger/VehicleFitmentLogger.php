<?php

/**
 * @package   Infosys/VehicleFitment
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleFitment\Logger;

use Infosys\VehicleFitment\Helper\Data;

/**
 * Class to log vehicle fitment calculations
 */
class VehicleFitmentLogger extends \Monolog\Logger
{
    protected $helper;

    /**
     * Constructor function
     *
     * @param string             $name       The logging channel
     * @param Data               $helper     Helper class
     * @param HandlerInterface[] $handlers   Optional stack of handlers
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct(
        $name,
        Data $helper,
        array $handlers = [],
        array $processors = []
    ) {
        $this->helper = $helper;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Log info
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = [])
    {
        if ($this->helper->isLogEnabled()) {
            parent::info($message, $context);
        }
    }
}
