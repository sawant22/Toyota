<?php
/**
 * @package     Infosys/ShippingRestriction
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */


namespace Infosys\ShippingRestriction\Helper;

use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data as JsonData;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Directory data helper
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Region extends \Magento\Directory\Helper\Data
{
    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Config $configCacheType
     * @param Collection $countryCollection
     * @param CollectionFactory $regCollectionFactory
     * @param JsonData $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        Context $context,
        Config $configCacheType,
        Collection $countryCollection,
        CollectionFactory $regCollectionFactory,
        JsonData $jsonHelper,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory
    ) {
        parent::__construct(
            $context,
            $configCacheType,
            $countryCollection,
            $regCollectionFactory,
            $jsonHelper,
            $storeManager,
            $currencyFactory
        );
    }

    /**
     * Retrieve regions data
     *
     * @param string $addressType
     * @return array
     */
    public function getRegionData($addressType = "")
    {
        $scope = $this->getCurrentScope();
        $allowedCountries = $this->scopeConfig->getValue(
            AllowedCountries::ALLOWED_COUNTRIES_PATH,
            $scope['type'],
            $scope['value']
        );
        $countryIds = explode(',', $allowedCountries);
        $collection = $this->_regCollectionFactory->create();
        $collection->addCountryFilter($countryIds)->load();
        $regions = [
            'config' => [
                'show_all_regions' => $this->isShowNonRequiredState(),
                'regions_required' => $this->getCountriesWithStatesRequired(),
            ],
        ];
        foreach ($collection as $region) {
            /** @var $region \Magento\Directory\Model\Region */
            if (!$region->getRegionId()) {
                continue;
            }

            if ($region->getCountryId() == "US" && $addressType =="order-shipping_address_region_id") {
                $xmlPath = 'checkout/state_filter/us_state_filter';
                $allowedStates = $this->scopeConfig->getValue($xmlPath, ScopeInterface::SCOPE_STORE);
                $allowedUsStates = explode(",", $allowedStates);
                if (!(in_array($region->getName(), $allowedUsStates))) {
                    continue;
                }
            }
            $regions[$region->getCountryId()][$region->getRegionId()] = [
                'code' => $region->getCode(),
                'name' => (string)__($region->getName()),
            ];
        }
        return $regions;
    }

    /**
     * Retrieve regions data json
     *
     * @param string $addressType
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegionJson($addressType = "")
    {
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, ['group' => 'TEST', 'method' => __METHOD__]);
            $scope = $this->getCurrentScope();
            $scopeKey = $scope['value'] ? '_' . implode('_', $scope) : null;
            $cacheKey = 'DIRECTORY_REGIONS_JSON_STORE' . $scopeKey;
        if ($addressType =="order-shipping_address_region_id") {
            $cacheKey = 'DIRECTORY_REGIONS_SHIPPING_JSON_STORE' . $scopeKey;
        }
            $json = $this->_configCacheType->load($cacheKey);
        if (empty($json)) {
            $regions = $this->getRegionData($addressType);
            $json = $this->jsonHelper->jsonEncode($regions);
            if ($json === false) {
                    $json = 'false';
            }
                  $this->_configCacheType->save($json, $cacheKey);
        }
            $this->_regionJson = $json;
        \Magento\Framework\Profiler::stop('TEST: ' . __METHOD__);
        return $this->_regionJson;
    }

    /**
     * Get current scope from request
     *
     * @return array
     */
    private function getCurrentScope(): array
    {
        $scope = [
            'type' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            'value' => null,
        ];
        $request = $this->_getRequest();
        if ($request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $scope = [
                'type' => ScopeInterface::SCOPE_WEBSITE,
                'value' => $request->getParam(ScopeInterface::SCOPE_WEBSITE),
            ];
        } elseif ($request->getParam(ScopeInterface::SCOPE_STORE)) {
            $scope = [
                'type' => ScopeInterface::SCOPE_STORE,
                'value' => $request->getParam(ScopeInterface::SCOPE_STORE),
            ];
        }

        return $scope;
    }
}
