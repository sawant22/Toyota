<?php

/**
 * @package     Infosys/StorePickUp
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\StorePickUp\Plugin\ShippingAddress;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\ResourceModel\Region\Collection;

class AvailableShippingMethods
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var Collection
     */
    protected $region;

    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CountryFactory $countryFactory
     * @param Collection $region
     * @return void
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CountryFactory $countryFactory,
        Collection $region
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->countryFactory = $countryFactory;
        $this->region = $region;
    }
    /**
     * Overriding the method to include cards details
     *
     * @param \Magento\SalesGraphQl\Model\Order\OrderPayments $subject
     * @param array $result
     * @param OrderInterface $orderModel
     * @return array
     */
    public function afterResolve(
        \Magento\QuoteGraphQl\Model\Resolver\ShippingAddress\AvailableShippingMethods $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $shippingMethods = [];
        foreach ($result as $method) {
            $pickupLocation = [
                'store_name' => '',
                'street_address_line1' => '',
                'street_address_line2' => '',
                'region_name' => '',
                'region_code' => '',
                'city' => '',
                'country' => '',
                'postcode' => '',
                'available_time_details' => '',
            ];
            if (isset($method['carrier_code']) &&  $method['carrier_code'] == 'dealerstore') {
                $store = $context->getExtensionAttributes()->getStore();
                $dealerName = $store->getWebsite()->getName();
                $storeId = $store->getId();
                $streetAddressLine1 = $this->scopeConfig->getValue(
                    'shipping/origin/street_line1',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $streetAddressLine2 = $this->scopeConfig->getValue(
                    'shipping/origin/street_line2',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $regionId = $this->scopeConfig->getValue(
                    'shipping/origin/region_id',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $city = $this->scopeConfig->getValue(
                    'shipping/origin/city',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $countrycode = $this->scopeConfig->getValue(
                    'shipping/origin/country_id',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $postcode = $this->scopeConfig->getValue(
                    'shipping/origin/postcode',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $availableTimeDetails = $this->scopeConfig->getValue(
                    'shipping/origin/store_available_details',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $regionData = $this->getRegionCode($regionId);
                $pickupLocation = [
                    'store_name' => $dealerName,
                    'street_address_line1' => $streetAddressLine1,
                    'street_address_line2' => $streetAddressLine2,
                    'region_name' => $regionData['region_name'],
                    'region_code' => $regionData['region_code'],
                    'city' => $city,
                    'country' => $this->getCountryName($countrycode),
                    'postcode' => $postcode,
                    'available_time_details' => $availableTimeDetails,
                ];
            }
            $shippingMethod = $method;
            $shippingMethod['pickup_address'] = $pickupLocation;
            $shippingMethods[] = $shippingMethod;
        }
        return $shippingMethods;
    }

    /**
     * Function to get country name
     *
     * @param string $countryCode
     * @return string
     */
    public function getCountryName($countryCode)
    {
        if ($countryCode) {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            return $country->getName();
        }
    }

    /**
     * Function to get region code
     *
     * @param int $regionId
     * @return string
     */
    public function getRegionCode($regionId)
    {
        $regionData = ['region_code' => '', 'region_name' => ''];
        if ($regionId) {
            $region = $this->region->getItemById($regionId);
            $regionData['region_code'] = $region->getCode();
            $regionData['region_name'] = $region->getName();
        }
        return $regionData;
    }
}
