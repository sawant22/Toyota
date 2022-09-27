<?php

/**
 * @package Infosys/DealerShippingCost
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DealerShippingCost\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

/**
 * Class to get shipstation store configuration values
 */
class Data extends AbstractHelper
{
    const XML_LOG_ENABLED = 'shipstation_general/logging_errors/active';

    const XML_SHIPSTATION_ENABLED = 'carriers/shipstation/active';

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
     * Log enabled/disabled function
     *
     * @return string
     */
    public function isLogEnabled(): string
    {
        $isEnabled = $this->scopeConfig->getValue(
            self::XML_LOG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        return $isEnabled;
    }

    /**
     * Function to get shipstation Enabled/Disabled for a store
     *
     * @param int $websiteId
     * @return string
     */
    public function isShipstationEnabled($websiteId): string
    {
        return $this->scopeConfig->getValue(
            self::XML_SHIPSTATION_ENABLED,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
