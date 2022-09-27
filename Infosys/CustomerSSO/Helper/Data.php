<?php

/**
 * @package     Infosys/AdminRole
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CustomerSSO\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class to create new website
 */
class Data extends AbstractHelper
{
    const XML_LOG_ENABLED = 'dcs/logging_errors/active';

    const SSO_URL = 'dcs/sso_redirect/sso_redirect_url';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */

    /**
     * Construct function
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * Log enabled/disbled function
     *
     * @return boolean
     */
    public function isLogEnabled()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $isEnabled = $this->scopeConfig->getValue(self::XML_LOG_ENABLED, $storeScope);
        return $isEnabled;
    }
    /**
     * get sso logout redirection url
     *
     * @return boolean
     */
    public function getSsoRedirectionUrl($storeId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $getUrl = $this->scopeConfig->getValue(self::SSO_URL, $storeScope, $storeId);
        return $getUrl;
    }
}
