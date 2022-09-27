<?php
/**
 * @package     Infosys/ShippingRestriction
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\ShippingRestriction\Model\Config\Region;

use \Magento\Directory\Api\CountryInformationAcquirerInterface;

class RegionInformationProvider
{
    /**
     * CountryInformation
     *
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInformationAcquirer;

    /**
     * Constructor function
     *
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     */
    public function __construct(CountryInformationAcquirerInterface $countryInformationAcquirer)
    {
        $this->countryInformationAcquirer = $countryInformationAcquirer;
    }

    /**
     * Get of Regions
     *
     * @return array
     */
    public function toOptionArray()
    {
        $country = $this->countryInformationAcquirer->getCountryInfo("US");
        $regions = [];
        if ($availableRegions = $country->getAvailableRegions()) {
            foreach ($availableRegions as $region) {
                $regions[] = [
                    'value' => $region->getName(),
                    'label' => $region->getName(),
                ];
            }
        }
        return $regions;
    }
}
