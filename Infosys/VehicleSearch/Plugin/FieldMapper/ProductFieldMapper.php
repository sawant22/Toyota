<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\VehicleSearch\Plugin\FieldMapper;

use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapper as ProductMapper;

class ProductFieldMapper
{

    /**
     * Overiding the metod to add vehicle product attribute type
     *
     * @param ProductMapper $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllAttributesTypes(ProductMapper $subject, $result)
    {
        $result['model_year'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['model_code'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['model_year_code'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['vehicle_entity_id'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['brand'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['series_name'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['grade'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['driveline'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['body_style'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['engine_type'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        $result['transmission'] =  [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ];
        return $result;
    }
}
