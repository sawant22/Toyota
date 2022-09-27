<?php

/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\PriceAdjustment\Model\Config;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Configurations for Price Adjustment
 */
class Configuration
{
    const ENABLE_DEALER_PRICE_CALC_CRON = 'discount/discount_configuration/price_calc_cronjob';

    const ENABLE_TIER_PRICE_IMPORT_DURING_PRODUCT_IMPORT= 'discount/discount_configuration/tier_price_import';
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
     * Enable/Disable dealer price calculation during Product Import
     * @return bool
     */
    public function enableDealerPriceCalcDuringImport(): bool
    {
        $enable = $this->scopeConfig->isSetFlag(self::ENABLE_DEALER_PRICE_CALC_CRON, ScopeInterface::SCOPE_STORE);
        return $enable;
    }
    
     /**
     * Enable/Disable Tier Price Import During Product Import
     * @return bool
     */
    public function enableTierPriceImportDuringImport(): bool
    {
        return $this->scopeConfig->isSetFlag(self::ENABLE_TIER_PRICE_IMPORT_DURING_PRODUCT_IMPORT, ScopeInterface::SCOPE_STORE);
    }
}
