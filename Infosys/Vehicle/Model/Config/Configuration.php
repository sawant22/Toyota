<?php

/**
 * @package     Infosys/Vehicle
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Vehicle\Model\Config;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Configurations for vehicle module
 */
class Configuration
{
    const ENABLE_VEHICLE_PRODUCT_MAPPING_IMPORT = 'epc_config/import_settings/mapping_import';

    const ENABLE_VEHICLE_FITMENT_IMPORT = 'epc_config/import_settings/fitment_import';

    const ENABLE_VEHICLE_FITMENT_CRON = 'epc_config/import_settings/fitment_calc_cron';

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
     * Enable/Disable insert records into Vehicle Product Mapping table during Product Import
     * @return bool
     */
    public function enableVehicleProductMappingImport(): bool
    {
        $enable = $this->scopeConfig->isSetFlag(self::ENABLE_VEHICLE_PRODUCT_MAPPING_IMPORT, ScopeInterface::SCOPE_STORE);
        return $enable;
    }

    /**
     * Enable/Disable insert records into Vehicle fits Queue table during Product Import
     * @return bool
     */
    public function enableVehicleFitmentImport(): bool
    {
        $enable = $this->scopeConfig->isSetFlag(
            self::ENABLE_VEHICLE_FITMENT_IMPORT,
            ScopeInterface::SCOPE_STORE
        );
        return $enable;
    }

    /**
     * Enable/Disable vehcle fitment calcualtion cron job function during Product Import
     * @return bool
     */
    public function enableVehicleFitmentCalcCron(): bool
    {
        $enable = $this->scopeConfig->isSetFlag(
            self::ENABLE_VEHICLE_FITMENT_CRON,
            ScopeInterface::SCOPE_STORE
        );
        return $enable;
    }
}
