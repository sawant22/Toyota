<?php
/**
 * @package     Infosys/Vehicle
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\Config\Brand;

/**
 * Class to provide brand information
 */
class BrandDataProvider
{
    /**
     * Brands list
     *
     * @return array
     */
    public function toOptionArray()
    {
        $brands = [
            [
                'value' => 'TOYOTA',
                'label' => 'TOYOTA'
            ],
            [
                'value' => 'LEXUS',
                'label' => 'LEXUS'
            ],
            [
                'value' => 'SCION',
                'label' => 'SCION'
            ]
        ];
        return $brands;
    }
}
