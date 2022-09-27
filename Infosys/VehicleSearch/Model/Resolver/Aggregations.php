<?php

/**
 * @package Infosys/VehicleSearch
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Infosys\VehicleSearch\Model\Resolver\Vehicles\Query\Search;
use Infosys\VehicleSearch\Helper\VehicleData;

/**
 * Layered navigation filters resolver, used for GraphQL request processing.
 */
class Aggregations implements ResolverInterface
{
    /**
     * @var Layer\DataProvider\Filters
     */
    private $filtersDataProvider;

    /**
     * @var LayerBuilder
     */
    private $layerBuilder;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var Search
     */
    private $vehicleSearchQuery;

    /**
     * @var VehicleData
     */
    private $vehicleData;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /**
     * @param \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider
     * @param LayerBuilder $layerBuilder
     * @param Search $vehicleSearchQuery
     * @param VehicleData $vehicleData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider,
        LayerBuilder $layerBuilder,
        Search $vehicleSearchQuery,
        VehicleData $vehicleData,
        \Magento\Eav\Model\Config $eavConfig,
        PriceCurrency $priceCurrency = null
    ) {
        $this->filtersDataProvider = $filtersDataProvider;
        $this->layerBuilder = $layerBuilder;
        $this->vehicleSearchQuery = $vehicleSearchQuery;
        $this->vehicleData = $vehicleData;
        $this->_eavConfig = $eavConfig;
        $this->priceCurrency = $priceCurrency ?: ObjectManager::getInstance()->get(PriceCurrency::class);
    }

    /**
     * Product aggregations resolver
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['layer_type']) || !isset($value['search_result'])) {
            return null;
        }

        $inputs = $value['inputs'];
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = (int)$store->getId();

        $aggregations = $value['search_result']->getSearchAggregation();

        if ($aggregations) {

            $results = $this->layerBuilder->build($aggregations, $storeId);

            //check if any vehicle attribute passed as products filter
            if ($this->vehicleData->hasVehicleAttributes($inputs['filter'])) {
                if (isset($inputs['sort'])) {
                    unset($inputs['sort']);
                }
                if (isset($inputs['search'])) {
                    unset($inputs['search']);
                }

                $modelYearCodeFilterOverrideEnabled = $this->vehicleData->isModelYearCodeOverride();
                $modelYearCodeFilterExists = array_key_exists('model_year_code', $inputs['filter'])
                    && array_key_exists('eq', $inputs['filter']['model_year_code']);

                //if we have model_year_code strip out other vehicle filters
                if ($modelYearCodeFilterOverrideEnabled && $modelYearCodeFilterExists) {
                    //save model_year and model_code for later
                    $modelYearCode = explode(':', $inputs['filter']['model_year_code']['eq']);
                    $modelYear = $modelYearCode[0];
                    $modelCode = $modelYearCode[1];

                    //Remove vehicle attribute filters
                    $vehicleAttributes = $this->vehicleData->getVehicleAttributes();
                    foreach($vehicleAttributes as $vehicleAttribute) {
                        unset($inputs['filter'][$vehicleAttribute]);
                    }

                    //Add back in model_year and model_code
                    $inputs['filter']['model_year'] = ["eq" => $modelYear];
                    $inputs['filter']['model_code'] = ["eq" => $modelCode];
                }

                //vehicle search query to get vehicle aggregations
                $searchResult = $this->vehicleSearchQuery->getResult($inputs, $info, $context);
                $vehicle_aggregations = $searchResult->getSearchAggregation();

                if ($vehicle_aggregations) {
                    $vehicle_results = $this->layerBuilder->build($vehicle_aggregations, $storeId);

                    // filtered out products aggregations as per vehicle aggregations results
                    foreach ($results as $key => $value) {
                        if (array_key_exists($key, $vehicle_results)) {
                            $common_options = [];
                            $productOptions = $value['options'];
                            $vehicleOptions = $vehicle_results[$key]['options'];
                            $common_options = array_uintersect($productOptions, $vehicleOptions, function ($prod, $vehicle) {
                                return strcmp($prod['value'], $vehicle['value']);
                            });
                            $results[$key]['count'] = count($common_options);
                            $results[$key]['options'] = $common_options;
                        }
                    }
                }
            }

            if (isset($results['price_bucket'])) {
                foreach ($results['price_bucket']['options'] as &$value) {
                    list($from, $to) = explode('-', $value['label']);
                    $newLabel = $this->priceCurrency->convertAndRound($from)
                        . '-'
                        . $this->priceCurrency->convertAndRound($to);
                    $value['label'] = $newLabel;
                    $value['value'] = str_replace('-', '_', $newLabel);
                }
            }
            //change vehicle aggregation label and code
            $vehicleAttributes = [];
            $productAttributes = [];

            foreach ($results as $aggregation => &$value) {
                if (isset($value['attribute_code'])) {
                    if (strpos($value['attribute_code'], '_bucket') !== false) {
                        $vehicleAttributes[$aggregation] = &$value;
                    } else {
                        $productAttributes[$aggregation] = &$value;
                    }
                }
            }

            $results = array_merge($vehicleAttributes, $productAttributes);

            if (
                $this->vehicleData->getConfig('epc_config/vehicle_aggregations/model_year') &&
                isset($results['model_year_bucket'])
            ) {
                $results['model_year_bucket']['label'] = 'Year';
                $results['model_year_bucket']['attribute_code'] = 'model_year';
                rsort($results['model_year_bucket']['options'], SORT_REGULAR);
            } else {
                unset($results['model_year_bucket']);
            }
            if (
                $this->vehicleData->getConfig('epc_config/vehicle_aggregations/series_name') &&
                isset($results['series_name_bucket'])
            ) {
                $results['series_name_bucket']['label'] = 'Model';
                $results['series_name_bucket']['attribute_code'] = 'series_name';
            } else {
                unset($results['series_name_bucket']);
            }
            if (
                $this->vehicleData->getConfig('epc_config/vehicle_aggregations/grade') &&
                isset($results['grade_bucket'])
            ) {
                $results['grade_bucket']['label'] = 'Trim Level';
                $results['grade_bucket']['attribute_code'] = 'grade';
            } else {
                unset($results['grade_bucket']);
            }
            if (
                $this->vehicleData->getConfig('epc_config/vehicle_aggregations/driveline') &&
                isset($results['driveline_bucket'])
            ) {
                $results['driveline_bucket']['label'] = 'Driveline';
                $results['driveline_bucket']['attribute_code'] = 'driveline';
            } else {
                unset($results['driveline_bucket']);
            }
            if (
                $this->vehicleData->getConfig('epc_config/vehicle_aggregations/body_style') &&
                isset($results['body_style_bucket'])
            ) {
                $results['body_style_bucket']['label'] = 'Body Style';
                $results['body_style_bucket']['attribute_code'] = 'body_style';
            } else {
                unset($results['body_style_bucket']);
            }
            if (
                $this->vehicleData->getConfig('epc_config/vehicle_aggregations/engine_type') &&
                isset($results['engine_type_bucket'])
            ) {
                $results['engine_type_bucket']['label'] = 'Engine Type';
                $results['engine_type_bucket']['attribute_code'] = 'engine_type';
            } else {
                unset($results['engine_type_bucket']);
            }
            if (
                $this->vehicleData->getConfig('epc_config/vehicle_aggregations/transmission') &&
                isset($results['transmission_bucket'])
            ) {
                $results['transmission_bucket']['label'] = 'Transmission';
                $results['transmission_bucket']['attribute_code'] = 'transmission';
            } else {
                unset($results['transmission_bucket']);
            }

            //Replace attribute code and label for product attributes
            foreach ($results as $aggregation => &$value) {
                if (isset($value['attribute_code'])) {
                    if (strpos($value['attribute_code'], '_bucket') !== false) {
                        $value['attribute_code'] = \preg_replace('~_bucket$~', '', $value['attribute_code']);
                        $attributeObj = $this->_eavConfig->getAttribute('catalog_product', $value['attribute_code']);
                        $value['label'] = $attributeObj->getFrontendLabel();
                    }
                }
            }

            return $results;
        } else {
            return [];
        }
    }
}
