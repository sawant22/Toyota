<?php

/**
 * @package   Infosys/SalesReport
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Logger;

use Infosys\SalesReport\Model\SalesReportStoreConfig;
use Monolog\Logger;

/**
 * Logger class for sales report logs
 */
class SalesReportLogger extends Logger
{
    protected SalesReportStoreConfig $storeConfig;

    /**
     * Constructor function
     *
     * @param string $name
     * @param SalesReportStoreConfig $storeConfig
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        $name,
        SalesReportStoreConfig $storeConfig,
        array $handlers = [],
        array $processors = []
    ) {
        $this->storeConfig = $storeConfig;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Sales Report logger info
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        if ($this->storeConfig->isLogEnabled()) {
            parent::info($message, $context);
        }
    }
}
