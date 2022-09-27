<?php

/**
 * @package Infosys/VehicleFitment
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleFitment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Class for vehicle fitment helper methods
 */
class Data extends AbstractHelper
{
    const XML_LOG_ENABLED = 'vehicle_fitment_config/logging_errors/active';

    /**
     * Constructor function
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Check if log enabled
     *
     * @return bool
     */
    public function isLogEnabled() : bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_LOG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config data
     *
     * @param string $config_path
     * @return void
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
