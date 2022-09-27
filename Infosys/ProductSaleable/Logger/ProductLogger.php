<?php

/**
 * @package   Infosys/ProductSaleable
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\ProductSaleable\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Monolog\Logger;

class ProductLogger extends Logger
{
    const XML_THRESHOLD_PRICE_LOG = 'threshold_price_config/logs/logs_active';

    protected ScopeConfigInterface $scopeConfig;

    /**
     * Constructor function
     *
     * @param string $name
     * @param ScopeConfigInterface $scopeConfig
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        $name,
        ScopeConfigInterface $scopeConfig,
        array $handlers = [],
        array $processors = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Log enabled/disbled function
     *
     * @return bool
     */
    public function isLogEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_THRESHOLD_PRICE_LOG);
    }

    /**
     * Log info function
     *
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = []): void
    {
        if ($this->isLogEnabled()) {
            parent::info($message, $context);
        }
    }
}
