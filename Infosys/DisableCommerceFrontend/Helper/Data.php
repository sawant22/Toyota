<?php

/**
 * @package Infosys/DisableCommerceFrontend
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DisableCommerceFrontend\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * helper class for config values
 */
class Data extends AbstractHelper
{
    const XML_ENABLED_COMMERECE_FRONTEND = 'disable_commerce_frontend/general/disable_frontend';

    const LOGOUT_URL = 'dcs/sso_redirect/sso_redirect_url';

    const XML_DISABLE_COMMERCE_HOMEPAGE = 'disable_commerce_frontend/general/disable_homepage';

    /**
     * Get logout redirection url
     *
     * @return string
     */
    public function getRedirectionUrl(): string
    {
        $getUrl = $this->scopeConfig->getValue(self::LOGOUT_URL, ScopeInterface::SCOPE_STORE);
        return $getUrl;
    }

    /**
     * Get commerece configuration
     * 
     * @return boolean
     */
    public function isFrontendEnabled(): bool
    {
        $isEnabled = $this->scopeConfig->isSetFlag(
            self::XML_ENABLED_COMMERECE_FRONTEND,
            ScopeInterface::SCOPE_STORE
        );
        return $isEnabled;
    }

    /**
     * Function to get homepage enabled/disabled config
     *
     * @return boolean
     */
    public function isHomePageEnabled(): bool
    {
        $isEnabled = $this->scopeConfig->isSetFlag(
            self::XML_DISABLE_COMMERCE_HOMEPAGE,
            ScopeInterface::SCOPE_STORE
        );
        return $isEnabled;
    }
}
