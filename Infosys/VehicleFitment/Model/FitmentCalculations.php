<?php

/**
 * @package     Infosys/VehicleFitment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleFitment\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Infosys\Vehicle\Model\Config\Brand\BrandDataProvider;
use Infosys\Vehicle\Model\VehicleFactory;
use Infosys\VehicleFitment\Helper\Data;
use Infosys\VehicleFitment\Logger\VehicleFitmentLogger;

/**
 * Class for fitment calculations
 */
class FitmentCalculations
{
    const CATALOG_TABLE = "catalog_product_entity_text";

    protected Json $json;

    protected VehicleFactory $vehicleFactory;

    protected BrandDataProvider $brandDataProvider;

    protected CollectionFactory $productCollectionFactory;

    protected ResourceConnection $resource;

    protected Attribute $eavModel;

    protected Data $helper;

    protected VehicleFitmentLogger $vehicleFitmentLogger;
   
    protected $_allBrands = [];

    /**
     * Constructor function
     *
     * @param Json $json
     * @param VehicleFactory $vehicleFactory
     * @param BrandDataProvider $brandDataProvider
     * @param CollectionFactory $productCollectionFactory
     * @param ResourceConnection $resource
     * @param Attribute $eavModel
     * @param Data $helper
     * @param VehicleFitmentLogger $vehicleFitmentLogger
     */
    public function __construct(
        Json $json,
        VehicleFactory $vehicleFactory,
        BrandDataProvider $brandDataProvider,
        CollectionFactory $productCollectionFactory,
        ResourceConnection $resource,
        Attribute $eavModel,
        Data $helper,
        VehicleFitmentLogger $vehicleFitmentLogger
    ) {
        $this->json = $json;
        $this->vehicleFactory = $vehicleFactory;
        $this->brandDataProvider = $brandDataProvider;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->eavModel = $eavModel;
        $this->helper = $helper;
        $this->vehicleFitmentLogger = $vehicleFitmentLogger;
        $this->_initBrands();
    }

   /**
    * Method to calculate vehicle fitment of a product
    *
    * @param array $productIds
    * @return void
    */
    public function updateProductsFitsData($productIds)
    {
        $attributeId = $this->eavModel->getIdByCode('catalog_product', 'what_this_fits');
        $batchSize = $this->helper->getConfig('vehicle_fitment_config/general/batch_size');

        $collection = $this->productCollectionFactory->create()->addAttributeToFilter('status', Status::STATUS_ENABLED);
        
        //unique product ids
        if (is_array($productIds)) {
            $productIds = array_unique($productIds);
            $collection = $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(['entity_id', 'row_id']);
        $productData = $collection->getData();

        //process data in batches
        foreach ($this->createBatch($productData, $batchSize) as $batchData) {
            $this->vehicleFitmentLogger->info("batch -  " . json_encode($batchData));
            $tableData = [];

            foreach ($batchData as $data) {
                $productId = $data['entity_id'];
                $rowId = $data['row_id'];
                $vehicleFits = $this->getVehicleFitsData($productId);
                if (count($vehicleFits) > 0) {
                    $whatThisFits = $this->json->serialize($vehicleFits);
                    $this->vehicleFitmentLogger->info("Vehicle Fitment Data for " . $productId . "is " . $whatThisFits);
                    $tableData[] = [
                        'row_id' => $rowId,
                        'attribute_id' => $attributeId,
                        'value' => $whatThisFits,
                    ];
                }
            }
    
            //insert fitment data
            if ($tableData) {
                $this->_connection->insertOnDuplicate(self::CATALOG_TABLE, $tableData, ['value']);
            }
        }
    }

    /**
     * Method to create batches
     *
     * @param array $data
     * @param int $size
     * @return array
     */
    public function createBatch($data, $size)
    {
        $i = 0;
        $batch = [];

        foreach ($data as $k => $v) {
            $batch[$k] = $v;
            if (++$i == $size) {
                yield $batch;
                $i = 0;
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            yield $batch;
        }
    }

    /**
     * Get vehicle fitment of a product
     *
     * @param int $productId
     * @return array
     */
    public function getVehicleFitsData($productId): array
    {
        $vehicleFitsArray = [];
        $brands = $this->_allBrands;
        
        $collection = $this->vehicleFactory->create();
        $vehicleCollection = $collection->getCollection()->addFieldToSelect('entity_id');
        $vehicleCollection->getSelect()->join(
            'catalog_vehicle_product',
            "main_table.entity_id = catalog_vehicle_product.vehicle_id",
            [
                'brand' => 'main_table.brand',
                'variant' => 'COUNT(main_table.entity_id)',
                'models' => 'COUNT(DISTINCT main_table.series_name)',
                'min_year' => 'MIN(main_table.model_year)',
                'max_year' => 'MAX(main_table.model_year)'
            ]
        )
            ->where('catalog_vehicle_product.product_id = (?) ', $productId)
            ->where('main_table.brand IN (?) ', $brands)
            ->group('main_table.brand');
        $data = $vehicleCollection->getData();
        if (!empty($data)) {
            foreach ($data as $vehicle) {
                $vehicleFitsArray[$vehicle['brand']] = [
                    'brand' => $vehicle['brand'],
                    'total_vehicles' => $vehicle['variant'],
                    'total_models' => $vehicle['models'],
                    'min_year' => $vehicle['min_year'],
                    'max_year' => $vehicle['max_year']
                ];
            }
        }
        return $vehicleFitsArray;
    }

    /**
     * Initialize existent brands
     *
     * @return $this
     */
    protected function _initBrands()
    {
        $availableBrands = $this->brandDataProvider->toOptionArray();
        foreach ($availableBrands as $availableBrand) {
            $brands[] = $availableBrand['value'];
        }
        $this->_allBrands = $brands;
        return $this;
    }
}
