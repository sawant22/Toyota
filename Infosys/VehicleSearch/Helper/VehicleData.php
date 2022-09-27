<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\VehicleSearch\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Load vehicle attributes
 */
class VehicleData extends AbstractHelper
{

    const XML_VEHICLE_ENABLE_MODEL_YEAR_CODE_OVERRIDE = 'epc_config/vehicle_aggregations/model_year_code_override';

    /**
     * Vehicle Attributes
     */
    public function getVehicleAttributes()
    {
        $attributes = [
            'model_year',
            'model_code',
            'model_year_code',
            'series_name',
            'grade',
            'driveline',
            'body_style',
            'engine_type',
            'transmission'
        ];
        return $attributes;
    }

    /**
     * Returns if the given filter set has any vehicle filters
     *
     * @param [type] $filters
     * @return boolean
     */
    public function hasVehicleAttributes($filters): bool
    {
        //available vehicle attributes
        return count(array_intersect($this->getVehicleAttributes(), array_keys($filters))) > 0;
    }

    /**
     * Method to get store configurations
     *
     * @param string $config_path
     * @return void
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Method to get store configuration flag
     *
     * @param string $config_path
     * @return bool
     */
    public function isSetFlag($config_path): bool
    {
        return $this->scopeConfig->isSetFlag(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Configuration for model_year_code override
     *
     * @return boolean
     */
    public function isModelYearCodeOverride(): bool
    {
        return $this->isSetFlag(self::XML_VEHICLE_ENABLE_MODEL_YEAR_CODE_OVERRIDE);
    }
}
