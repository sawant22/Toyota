<?php

/**
 * @package Infosys/WebsiteProductsMapping
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\WebsiteProductsMapping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Helper class
 */
class Data extends AbstractHelper
{
    const XML_LOG_ENABLED = 'website_products_mapping/logging_errors/active';

    /**
     * Initialize dependencies
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Log enabled/disabled function
     *
     * @return string
     */
    public function isLogEnabled()
    {
        $isEnabled = $this->scopeConfig->getValue(
            self::XML_LOG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        return $isEnabled;
    }

    /**
     * Get config data for a store
     *
     * @param string $config_path
     * @return string
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
