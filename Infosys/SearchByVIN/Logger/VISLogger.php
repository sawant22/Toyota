<?php

/**
 * @package   Infosys/SearchByVIN
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\SearchByVIN\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;

class VISLogger extends \Monolog\Logger
{
    const XML_VIS_LOG_ENABLED = 'searchbyvin/logging_errors/active';
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param string             $name       The logging channel
     * @param ScopeConfigInterface $scopeConfig     Scope Config class
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
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
     * @return boolean
     */
    public function isLogEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_VIS_LOG_ENABLED);
    }

    /**
     * Log info function
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = [])
    {
        if ($this->isLogEnabled()) {
            parent::info($message, $context);
        }
    }
}
