<?php
/**
 * @package     Infosys/YMMSearchGraphQL
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\YMMSearchGraphQL\Model\Config\Brand;

/**
 * Class to provide vehicle image brand information
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
                'value' => 'TOY',
                'label' => 'TOY'
            ],
            [
                'value' => 'LEX',
                'label' => 'LEX'
            ]
        ];
        return $brands;
    }
}
