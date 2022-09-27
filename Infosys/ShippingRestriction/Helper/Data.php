<?php
/**
 * @package     Infosys/ShippingRestriction
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\ShippingRestriction\Helper;

use \Magento\Directory\Api\CountryInformationAcquirerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Exception\InputException;
use \Magento\Store\Model\ScopeInterface;

/**
 * Helper Data class
 */
class Data extends AbstractHelper
{
    const XML_PATH_STATE = "checkout/state_filter/us_state_filter";

    /**
     * Construct function
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        CountryInformationAcquirerInterface $countryInformationAcquirer
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
    }

    /**
     * GetActiveUSShippingStates function
     *
     * @return array
     */
    public function getActiveUSShippingStates()
    {
        $allowedStates = $this->scopeConfig->getValue(self::XML_PATH_STATE, ScopeInterface::SCOPE_STORE);
        $allowedStateList = explode(",", $allowedStates);

        $country = $this->countryInformationAcquirer->getCountryInfo("US");
        $regions = [];
        if ($availableRegions = $country->getAvailableRegions()) {
            foreach ($availableRegions as $region) {
                if (in_array($region->getName(), $allowedStateList)) {
                    $regions[] = [
                        'id' => $region->getId(),
                        'regioncode' => $region->getName(),
                        'region' => $region->getCode()
                    ];
                }
            }
        }
        return $regions;
    }

    /**
     * ValidateStateEnabled function
     *
     * @param string $regionName
     * @return bool
     */
    public function validateStateEnabled($regionName)
    {
        if ($regionName!="" && !$this->checkStateEnabled($regionName)) {
            throw new InputException(__('We do not ship to ' . $regionName . ' State.'));
        }
    }

    /**
     * CheckStateEnabled function
     *
     * @param string $regionName
     * @return bool
     */
    public function checkStateEnabled($regionName)
    {
        $allowedStates = $this->scopeConfig->getValue(self::XML_PATH_STATE, ScopeInterface::SCOPE_STORE);
        return in_array($regionName, explode(",", $allowedStates));
    }

    /**
     * CheckPOBoxAddress function
     *
     * @param object $address
     * @return void
     */
    public function checkPOBoxAddress($address)
    {
        if ($this->scopeConfig->getValue('checkout/state_filter/is_pobox_disabled', ScopeInterface::SCOPE_STORE)) {
            $streetList = $address->getStreet();
            foreach ($streetList as $street) {
                if (preg_match("/p\.* *o\.* *box/i", $street)) {
                    $msgpath = 'checkout/state_filter/is_pobox_message';
                    throw new InputException(
                        __($this->scopeConfig->getValue($msgpath, ScopeInterface::SCOPE_STORE))
                    );
                }
            }
        }
    }
    /**
     * GetPOBoxMessage function
     *
     * @return string
     */
    public function getPOBoxMessage()
    {
        $msgpath = 'checkout/state_filter/is_pobox_message';
        return $this->scopeConfig->getValue($msgpath, ScopeInterface::SCOPE_STORE);
    }
}
