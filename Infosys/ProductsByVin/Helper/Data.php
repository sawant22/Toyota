<?php
/**
 * @package Infosys/ProductsByVin
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\ProductsByVin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_BRAND_CONFIG = 'dealer_brand/brand_config/brand_filter';
    const XML_ENABLE_SEARCH_BY_VIN_CONFIG = 'epc_config/search_customizations/enable_search_by_vin';
    const XML_ENABLE_SUGGESTED_TERMS_CONFIG = 'epc_config/search_customizations/enable_search_suggestions';
    
    /**
     * @inheritdoc
     */
    public function getEnabledBrands($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_BRAND_CONFIG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isSearchByVinEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_ENABLE_SEARCH_BY_VIN_CONFIG);
    }

    public function isShowSuggestedTerms()
    {
        return $this->scopeConfig->isSetFlag(self::XML_ENABLE_SUGGESTED_TERMS_CONFIG);
    }
}
