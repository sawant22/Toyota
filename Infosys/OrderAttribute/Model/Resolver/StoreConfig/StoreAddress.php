<?php

/**
 * @package     Infosys/OrderAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\OrderAttribute\Model\Resolver\StoreConfig;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\ResourceModel\Region\Collection;

/**
 * Class to provide dealer information
 */
class StoreAddress implements ResolverInterface
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
     * Get Store Information in store config graphql
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $dealerName = $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $streetAddress1 = $this->scopeConfig->getValue(
            'general/store_information/street_line1',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $streetAddress2 = $this->scopeConfig->getValue(
            'general/store_information/street_line2',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $city = $this->scopeConfig->getValue(
            'general/store_information/city',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $regionId = $this->scopeConfig->getValue(
            'general/store_information/region_id',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $countrycode = $this->scopeConfig->getValue(
            'general/store_information/country_id',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $postcode = $this->scopeConfig->getValue(
            'general/store_information/postcode',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $phone = $this->scopeConfig->getValue(
            'general/store_information/phone',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $email = $this->scopeConfig->getValue(
            'general/store_information/store_email',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $regionData = $this->getRegionCode($regionId);
        $arr = [
            'dealer_name' => $dealerName,
            'street_address1' => $streetAddress1,
            'street_address2' => $streetAddress2,
            'city' => $city,
            'region_code' => $regionData['region_code'],
            'region_name' => $regionData['region_name'],
            'country' => $this->getCountryName($countrycode),
            'postcode' => $postcode,
            'phone_number' => $phone,
            'store_email' => $email
        ];
        return $arr;
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
