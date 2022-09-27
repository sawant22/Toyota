<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerCentral\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class CustomerCentralHandler extends \Magento\Framework\Logger\Handler\Base
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
    public $fileName = '/var/log/toyota_customercentral.log';
}
