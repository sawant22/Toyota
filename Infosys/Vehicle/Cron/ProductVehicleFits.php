<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Vehicle\Cron;

use Exception;
use Magento\Framework\Serialize\Serializer\Json;
use Infosys\Vehicle\Model\VehicleFitsQueueFactory;
use Infosys\Vehicle\Logger\VehicleLogger;
use Infosys\ProductsByVin\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ProductFactory;
use Infosys\Vehicle\Model\Config\Brand\BrandDataProvider;
use Infosys\Vehicle\Model\VehicleFactory;
use Infosys\Vehicle\Helper\Data as VehicleHelper;
use Magento\Framework\App\ResourceConnection;
use Infosys\Vehicle\Model\Config\Configuration;

/**
 * Class to update what this fits attribute for products
 */
class ProductVehicleFits
{
    protected VehicleLogger $vehicleLogger;

    protected Json $json;

    protected VehicleFitsQueueFactory $vehicleFitsQueueFactory;

    protected VehicleFactory $vehicleFactory;

    protected Data $helperData;

    protected StoreManagerInterface $storeManager;

    protected Action $action;

    protected ProductFactory $productFactory;

    protected BrandDataProvider $brandDataProvider;

    protected VehicleHelper $vehicleHelper;

    protected ResourceConnection $resourceConnection;

    protected Configuration $vehicleConfig;

    /**
     * Constructor function
     *
     * @param VehicleFitsQueueFactory $vehicleFitsQueueFactory
     * @param Json $json
     * @param VehicleLogger $vehicleLogger
     * @param VehicleFactory $vehicleFactory
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param Action $action
     * @param ProductFactory $productFactory
     * @param BrandDataProvider $brandDataProvider
     * @param VehicleHelper $vehicleHelper
     * @param ResourceConnection $resourceConnection
     * @param Configuration $vehicleConfig
     */
    public function __construct(
        VehicleFitsQueueFactory $vehicleFitsQueueFactory,
        Json $json,
        VehicleLogger $vehicleLogger,
        VehicleFactory $vehicleFactory,
        Data $helperData,
        StoreManagerInterface $storeManager,
        Action $action,
        ProductFactory $productFactory,
        BrandDataProvider $brandDataProvider,
        VehicleHelper $vehicleHelper,
        ResourceConnection $resourceConnection,
        Configuration $vehicleConfig
    ) {
        $this->vehicleFitsQueueFactory = $vehicleFitsQueueFactory;
        $this->json = $json;
        $this->vehicleLogger = $vehicleLogger;
        $this->vehicleFactory = $vehicleFactory;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->action = $action;
        $this->productFactory = $productFactory;
        $this->brandDataProvider = $brandDataProvider;
        $this->vehicleHelper = $vehicleHelper;
        $this->resourceConnection = $resourceConnection;
        $this->vehicleConfig = $vehicleConfig;
    }

    /**
     * Function to update what this fits attribute for products
     */
    public function execute()
    {
        $enableFitmentCalCron = $this->vehicleConfig->enableVehicleFitmentCalcCron();
        if ($enableFitmentCalCron) {
            $this->vehicleLogger->info("Vehicle fitment calcualtion cron is enabled");
            $maxCount = $this->vehicleHelper->getMaxFitmentProductsCount();
            $fitsQueue = $this->vehicleFitsQueueFactory->create();
            $fitsQueueCollection = $fitsQueue->getCollection();
            $fitsQueueCollection->getSelect()->group('product_id')
                ->group('store_id')
                ->order('entity_id', 'ASC')
                ->limit($maxCount);
            if (!empty($fitsQueueCollection)) {
                try {
                    foreach ($fitsQueueCollection as $queue) {
                        $productId = $queue->getProductId();
                        $this->vehicleLogger->info("product id from vehile fits table " . $productId);
                        $product = $this->productFactory->create()->load($productId);
                        if ($product->getId()) {
                            if (empty($queue->getProductFlag())) {
                                $websites = $product->getWebsiteIds();
                                array_unshift($websites, '0');
                                $vehicleFits = $this->getVehicleFitsData($productId);
                                foreach ($websites as $websiteId) {
                                    $whatThisFits = [];
                                    $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
                                    $dealer_brands = $this->helperData->getEnabledBrands($storeId);
                                    $dealer_brands = explode(',', $dealer_brands);
                                    foreach ($dealer_brands as $brand) {
                                        if (array_key_exists($brand, $vehicleFits)) {
                                            $whatThisFits[] = $vehicleFits[$brand];
                                        }
                                    }
                                    if ($whatThisFits) {
                                        $whatThisFits = $this->json->serialize($whatThisFits);
                                        $this->vehicleLogger->info("Product " . $productId . " vehicle fits data against website " . $websiteId . " is " . $whatThisFits);
                                        $updateAttributes['what_this_fits'] = $whatThisFits;
                                    } else {
                                        $this->vehicleLogger->info("No Fitment data for Product " . $productId . "in website " . $websiteId);
                                        $updateAttributes['what_this_fits'] = "";
                                    }
                                    $this->action->updateAttributes([$product->getId()], $updateAttributes, $storeId);
                                }
                            } else {
                                $whatThisFits = [];
                                $vehicleFits = $this->getVehicleFitsData($product->getId());
                                $dealer_brands = $this->helperData->getEnabledBrands($queue->getStoreId());
                                $dealer_brands = explode(',', $dealer_brands);
                                foreach ($dealer_brands as $brand) {
                                    if (array_key_exists($brand, $vehicleFits)) {
                                        $whatThisFits[] = $vehicleFits[$brand];
                                    }
                                }
                                if ($whatThisFits) {
                                    $whatThisFits = $this->json->serialize($whatThisFits);
                                    $this->vehicleLogger->info("Fitment data for " . $product->getId() . " is " . $whatThisFits);
                                    $updateAttributes['what_this_fits'] = $whatThisFits;
                                } else {
                                    $this->vehicleLogger->info("No Fitment data for " . $product->getId());
                                    $updateAttributes['what_this_fits'] = "";
                                }
                                $this->action->updateAttributes([$product->getId()], $updateAttributes, $queue->getStoreId());
                            }
                        }
                        $connection  = $this->resourceConnection->getConnection();
                        $tableName = $connection->getTableName('vehicle_fits_queue');

                        $whereConditions = [
                            $connection->quoteInto('product_id = ?', $productId),
                            $connection->quoteInto('store_id = ?', $queue->getStoreId()),
                        ];
                        $connection->delete($tableName, $whereConditions);
                    }
                } catch (\Exception $e) {
                    $this->vehicleLogger->error("Error in product vehicle fits: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Method to calculate vehicle aggregations of a product
     *
     * @param int $productId
     * @return array
     */
    public function getVehicleFitsData($productId): array
    {
        $vehicleFitsArray = [];
        $availableBrands = $this->brandDataProvider->toOptionArray();
        foreach ($availableBrands as $availableBrand) {
            $brands[] = $availableBrand['value'];
        }
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
}
