<?php

/**
 * @package   Infosys/DealerShippingCost
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DealerShippingCost\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Handler class for dealer shipping cost logs
 */
class ShippingCostHandler extends Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * Log file
     *
     * @var string
     */
    public $fileName = '/var/log/toyota_dealer_shipping_cost.log';
}
