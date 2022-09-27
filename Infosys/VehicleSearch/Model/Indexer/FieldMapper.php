<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Indexer;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface as FieldMapperInterface;

class FieldMapper implements FieldMapperInterface
{
    /**
     * Vehicle Fields mapping
     *
     * @var array
     */
    private $attributeTypes = [
        "body_style" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ],
        "brand" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ],
        "created_at" => [
            "type" => "date"
        ],
        "driveline" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ],
        "engine_type" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ],
        "entity_id" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ],
        "grade" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ],
        "model_code" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ],
        "model_year" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                ]
            ]
        ],
        "series_name" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "title" => [
            "type" => "text",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "transmission" => [
            "type" => "keyword",
            "fields" => [
                "keyword" => [
                    "type" => "keyword",
                    "ignore_above" => 256
                ]
            ]
        ],
        "updated_at" => [
            "type" => "date"
        ]
    ];
    /**
     * Get All Attrubute Type
     *
     * @param array $context
     * @return void
     */
    public function getAllAttributesTypes($context = [])
    {
        return $this->attributeTypes;
    }
    /**
     * Get Field Name
     *
     * @param string $attributeCode
     * @param array $context
     * @return void
     */
    public function getFieldName($attributeCode, $context = [])
    {
        throw new \LogicException('Not implemented');
    }
}
