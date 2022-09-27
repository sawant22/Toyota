<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Plugin;

use Magento\Elasticsearch\Model\Adapter\Elasticsearch;
use Magento\Framework\App\ResourceConnection;
use Infosys\Vehicle\Logger\VehicleLogger;
use Infosys\Vehicle\Helper\Data;
use Infosys\ProductsByVin\Helper\Data as BrandHelper;

/**
 * @SuppressWarnings(PHPMD)
 * @see \Magento\Elasticsearch\Model\Adapter\Elasticsearch::addDocs()
 */
class PushVehicleInProductsBeforeAddDocs
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var VehicleLogger
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var BrandHelper
     */
    protected $brandHelper;

    /**
     * Constructor function
     *
     * @param ResourceConnection $resource
     * @param VehicleLogger $logger
     * @param Data $helper
     * @param BrandHelper $brandHelper
     */
    public function __construct(
        ResourceConnection $resource,
        VehicleLogger $logger,
        Data $helper,
        BrandHelper $brandHelper
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->brandHelper = $brandHelper;
    }

    public function beforeAddDocs(Elasticsearch $subject, array $docs, int $storeId, string $mappedIndexerId)
    {
        if ($mappedIndexerId == 'vehicle_indexer_index') {
            return [$docs, $storeId, $mappedIndexerId];
        }
        if ($mappedIndexerId == "product") {
            $productIds = array_keys($docs);
            $vehiclesData = $this->getVehicleData($productIds, $storeId);
            foreach ($docs as $productId => $doc) {
                if (isset($vehiclesData[$productId])) {
                    $productVehiclesData =  $this->getProductVehiclesData($vehiclesData[$productId]);
                    $docs[$productId]['vehicle_entity_id'] = $productVehiclesData['vehicle_entity_id'];
                    $docs[$productId]['brand'] = $productVehiclesData['brand'];
                    $docs[$productId]['model_year'] = $productVehiclesData['model_year'];
                    $docs[$productId]['model_code'] = $productVehiclesData['model_code'];
                    $docs[$productId]['model_year_code'] = $productVehiclesData['model_year_code'];
                    $docs[$productId]['series_name'] = $productVehiclesData['series_name'];
                    $docs[$productId]['grade'] = $productVehiclesData['grade'];
                    $docs[$productId]['driveline'] = $productVehiclesData['driveline'];
                    $docs[$productId]['body_style'] = $productVehiclesData['body_style'];
                    $docs[$productId]['engine_type'] = $productVehiclesData['engine_type'];
                    $docs[$productId]['transmission'] = $productVehiclesData['transmission'];
                }
            }
        }
        return [$docs, $storeId, $mappedIndexerId];
    }

    /**
     * Get Product Vehicles data
     *
     * @param array $productIds
     * @param int $storeId
     * @return array
     */
    private function getVehicleData($productIds, $storeId): array
    {
        $vehicleData = [];
        $dealer_brand = $this->brandHelper->getEnabledBrands($storeId);
        $connection = $this->resource->getConnection();
        //get all vehicles data for dealer enabled brands
        $select = $connection->select()
        ->from(
            ['v' => 'catalog_vehicle_entity'],
            [
                'v.entity_id',
                'v.brand',
                'v.model_year',
                'v.model_code',
                'v.series_name',
                'v.grade',
                'v.driveline',
                'v.body_style',
                'v.engine_type',
                'v.transmission'
            ]
        )
        ->where('v.status ', 1);
        if (isset($dealer_brand)) {
            $brand = explode(',', $dealer_brand);
            $select->where('v.brand IN (?) ', $brand);
        }
        $vehicleData = $connection->fetchAll($select);
        $vehcileDataById = [];
        foreach($vehicleData as $vehicleInfo) {
            $vehcileDataById[$vehicleInfo['entity_id']] = $vehicleInfo;
        }
        
        $mappingVehicleProductData = [];
        $mappingSelect = $connection->select()
            ->from(
                ['vp' => 'catalog_vehicle_product'],
                [
                    'vp.product_id',
                    'vp.vehicle_id'
                ]
            )
            ->where('vp.product_id IN (?)', $productIds);
        $this->logger->info("Dealer Brand for Store" . $storeId . "is: " . $dealer_brand);
        
        $mappingVehicleProductData = $connection->fetchAll($mappingSelect);
        
        $vehicleDataByProduct = [];
        foreach ($mappingVehicleProductData as $mappingVehicleByProduct) {
            if(isset($vehcileDataById[$mappingVehicleByProduct['vehicle_id']])) {
                $vehicleDataByProduct[strval($mappingVehicleByProduct['product_id'])][] = $vehcileDataById[$mappingVehicleByProduct['vehicle_id']];
            }            
        }
        return $vehicleDataByProduct;
    }

    /**
     * Get Vehicles Data
     *
     * @param array $vehiclesData
     * @return array
     */
    private function getProductVehiclesData($vehiclesData)
    {
        $combineVehicleData = [];
        if ($vehiclesData && count($vehiclesData) > 0) {
            $vehicleEntityId = [];
            $brand = [];
            $modelYear = [];
            $modelCode = [];
            $modelYearCode = [];
            $seriesName = [];
            $grade = [];
            $driveline = [];
            $bodyStyle = [];
            $engineType = [];
            $transmission = [];
            //Loop the product vehicles data
            foreach ($vehiclesData as $individualProductVehicle) {
                $vehicleEntityId[] = $individualProductVehicle['entity_id'];
                $brand[] = $individualProductVehicle['brand'];
                $modelYear[] = $individualProductVehicle['model_year'];
                $modelCode[] = $individualProductVehicle['model_code'];
                $modelYearCode[] = $individualProductVehicle['model_year'] . ':' . $individualProductVehicle['model_code'];
                $seriesName[] = $individualProductVehicle['series_name'];
                $grade[] = $individualProductVehicle['grade'];
                $driveline[] = $individualProductVehicle['driveline'];
                $bodyStyle[] = $individualProductVehicle['body_style'];
                $engineType[] = $individualProductVehicle['engine_type'];
                $transmission[] = $individualProductVehicle['transmission'];
            }

            $combineVehicleData['vehicle_entity_id'] = array_values(array_unique($vehicleEntityId));
            $combineVehicleData['brand'] = array_values(array_unique($brand));
            $combineVehicleData['model_year'] = array_values(array_unique($modelYear));
            $combineVehicleData['model_code'] = array_values(array_unique($modelCode));
            $combineVehicleData['model_year_code'] = array_values(array_unique($modelYearCode));
            $combineVehicleData['series_name'] = array_values(array_unique($seriesName));
            $combineVehicleData['grade'] = array_values(array_unique($grade));
            $combineVehicleData['driveline'] = array_values(array_unique($driveline));
            $combineVehicleData['body_style'] = array_values(array_unique($bodyStyle));
            $combineVehicleData['engine_type'] = array_values(array_unique($engineType));
            $combineVehicleData['transmission'] = array_values(array_unique($transmission));
        }

        return $combineVehicleData;
    }
}
