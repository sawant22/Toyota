<?php

/**
 * @package   Infosys/DealerShippingCost
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DealerShippingCost\Logger;

use Infosys\DealerShippingCost\Helper\Data;
use Monolog\Logger;

/**
 * Logger class for dealer shipping cost logs
 */
class ShippingCostLogger extends Logger
{
    protected Data $helper;

    /**
     * Constructor function
     *
     * @param string $name
     * @param Data $helper
     * @param array $handlers
     * @param array $processors
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
     * Shipping cost logger info
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        if ($this->helper->isLogEnabled()) {
            parent::info($message, $context);
        }
    }
}
