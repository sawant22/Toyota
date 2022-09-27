<?php

/**
 * @package     Infosys/EPCconnect
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\EPCconnect\Model\Config;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Configurations for EPCconnect module
 */
class Configuration
{
    const XML_CANONICAL_URLREWRITE_GENEREATE = 'epcconnect/enable_product_import_methods/enable_canonical_urlrewrite_generate';

    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Enable/Disable Canonical URLRewrite Generate
     *
     */
    public function isCanonicalUrlrewriteGenerateEnabled(): bool
    {
        $enable = $this->scopeConfig->isSetFlag(
            self::XML_CANONICAL_URLREWRITE_GENEREATE,
            ScopeInterface::SCOPE_STORE
        );

        return $enable;
    }

}
