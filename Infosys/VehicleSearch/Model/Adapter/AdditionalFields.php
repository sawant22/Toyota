<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Adapter;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Framework\App\ResourceConnection;
use Infosys\Vehicle\Logger\VehicleLogger;
use Infosys\Vehicle\Helper\Data;
use Infosys\ProductsByVin\Helper\Data as BrandHelper;

class AdditionalFields implements AdditionalFieldsProviderInterface
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
    /**
     * Get the additional fields for product index
     *
     * @param array $productIds
     * @param int $storeId
     * @return void
     */
    public function getFields(array $productIds, $storeId)
    {
        $fields = [];
        $vehiclesData = $this->getVehicleData($productIds , $storeId);
        $this->logger->info("product Vehicle Data:".json_encode($vehiclesData));
        foreach ($productIds as $productId) {
            if(isset($vehiclesData[$productId])){
                $productVehiclesData =  $this->getProductVehiclesData($vehiclesData[$productId]);
                $fields[$productId] = $productVehiclesData;
            }    
        }
        return $fields;
    }

    /**
     * Get Product Vehicles data
     *
     * @param array $productIds
     * @param int $storeId
     * @return void
     */
    private function getVehicleData($productIds, $storeId)
    {
        $vehicleData = [];
        $dealer_brand = $this->brandHelper->getEnabledBrands($storeId);
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(['vp' => 'catalog_vehicle_product'])->join(['v' => 'catalog_vehicle_entity'], 'vp.vehicle_id=v.entity_id')
            ->where('vp.product_id IN (?)', $productIds);
			$this->logger->info("Dealer Brand for Store". $storeId . "is: " . $dealer_brand);
            if(isset($dealer_brand)) {
                $brand = explode(',', $dealer_brand);
                $select->where('v.brand IN (?) ', $brand);
            }
        $vehicleData = $connection->fetchAll($select);
        
        $vehicleDataByProduct= [];
        foreach($vehicleData as $vehicles) {
            $vehicleDataByProduct[strval($vehicles['product_id'])][] = $vehicles;
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
                foreach($vehiclesData as $individualProductVehicle){
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