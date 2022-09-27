<?php

/**
 * @package   Infosys/WebsiteProductsMapping
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\WebsiteProductsMapping\Logger;

use Infosys\WebsiteProductsMapping\Helper\Data;

/**
 * Logger class for website products mapping logs
 */
class ProductsMappingLogger extends \Monolog\Logger
{
    protected Data $helper;

    /**
     * Initialize dependencies
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
     * Log info function
     *
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = [])
    {
        if ($this->helper->isLogEnabled()) {
            parent::info($message, $context);
        }
    }
}
