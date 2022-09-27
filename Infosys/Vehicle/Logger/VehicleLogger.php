<?php

/**
 * @package   Infosys/Vehicle
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Logger;

use Infosys\Vehicle\Helper\Data;

class VehicleLogger extends \Monolog\Logger
{
    protected  $helper;

    /**
     * @param string             $name       The logging channel
     * @param Data               $helper     Helper class
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct(
        $name, 
        Data $helper,
        array $handlers = array(), 
        array $processors = array())
    {
        $this->helper = $helper;
        parent::__construct($name, $handlers, $processors);
    }


    public function info($message, array $context = array())
    {
        if ($this->helper->isLogEnabled()) {
            parent::info($message, $context);
        }

    }
}
