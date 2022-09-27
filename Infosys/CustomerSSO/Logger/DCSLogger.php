<?php

/**
 * @package   Infosys/CustomerSSO
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerSSO\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Infosys\CustomerSSO\Helper\Data;

class DCSLogger extends \Monolog\Logger
{

    protected  $scopeConfig;
    /**
     * @param string             $name       The logging channel
     * @param ScopeConfigInterface $scopeConfig     Scope Config class
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct(
        $name,
        ScopeConfigInterface $scopeConfig,
        array $handlers = array(),
        array $processors = array())
    {
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
        return $this->scopeConfig->isSetFlag(DATA::XML_LOG_ENABLED);
    }

    /**
     * Log info function
     *
     * @return string
     */

    public function info($message, array $context = array())
    {
        if ($this->isLogEnabled()) {
            parent::info($message, $context);
        }

    }

}
