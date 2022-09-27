<?php

/**
 * @package   Infosys/SalesReport
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Handler class for sales report logs
 */
class SalesReportHandler extends Base
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
    public $fileName = '/var/log/toyota_sales_report.log';
}
