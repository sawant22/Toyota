<?php

/**
 * @package   Infosys/SearchByVIN
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\SearchByVIN\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class VISHandler extends \Magento\Framework\Logger\Handler\Base
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
    public $fileName = '/var/log/toyota_vis.log';
}
