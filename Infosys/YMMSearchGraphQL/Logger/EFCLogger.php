<?php

/**
 * @package   Infosys/YMMSearchGraphQL
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\YMMSearchGraphQL\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;

class EFCLogger extends \Monolog\Logger
{
    const XML_EFC_LOG = 'searchbyYMM/logging_errors/logs_active';
    
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
        return $this->scopeConfig->isSetFlag(self::XML_EFC_LOG);
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
