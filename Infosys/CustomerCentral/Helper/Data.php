<?php

/**
 * @package Infosys/CustomerCentral
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\CustomerCentral\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

  /**
   * Data class to get configuration values
   */
class Data extends AbstractHelper
{
    const XML_LOG_ENABLED = 'customer_central/logging_errors/active';
    const XML_CC_ORDER_SYNC_CRON = 'customer_central/cc_order_sync_cron/retry_count';
    const XML_SEND_ORDER_INCREMENT_ID = 'customer_central/parts_online_purchase/active';

     /**
     * Is Logging Enabled
     *
     */
    public function isLogEnabled(): bool
    {
        $isEnabled = $this->scopeConfig->isSetFlag(
            self::XML_LOG_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $isEnabled;
    }

    /**
     * Get Retry Count
     *
     */
    public function getRetryCount(): string
    {
        $retryCount = $this->scopeConfig->getValue(
            self::XML_CC_ORDER_SYNC_CRON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $retryCount;
    }
    /**
     * Check Whether to Send Order Increment Id To Customer Central
     *
     */
    public function sendOrderIncrementId(): string
    {
        $sendOrderIncrementId = $this->scopeConfig->getValue(
            self::XML_SEND_ORDER_INCREMENT_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $sendOrderIncrementId;
    }
}
