<?php


/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\Vehicle\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Infosys\Vehicle\Model\VehicleFitsQueueFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\ResourceConnection;
use Infosys\Vehicle\Logger\VehicleLogger;

/**
 * Class to insert product ids into fits table when updating brand from admin
 */
class StoreBrandChange implements ObserverInterface
{
    const VEHICLE_FITS_QUEUE = "vehicle_fits_queue";

    protected VehicleFitsQueueFactory $vehicleFitsQueueFactory;

    protected StoreManagerInterface $storeManager;

    protected ResourceConnection $resource;

    protected ProductFactory $productFactory;

    protected VehicleLogger $logger;

    /**
     * Constructor function
     *
     * @param VehicleFitsQueueFactory $vehicleFitsQueueFactory
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param ProductFactory $productFactory
     * @param VehicleLogger $logger
     */
    public function __construct(
        VehicleFitsQueueFactory $vehicleFitsQueueFactory,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        ProductFactory $productFactory,
        VehicleLogger $logger
    ) {
        $this->vehicleFitsQueueFactory = $vehicleFitsQueueFactory;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->_connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->logger = $logger;
    }

    /**
     * Function to insert product id's into vehilce fits table when updating brand from admin
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        $websiteId = $observer->getWebsite();
        $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        try {
            $productCollection = $this->productFactory->create()->getCollection()
            ->addFieldToSelect('entity_id')
            ->addAttributeToFilter('status', Status::STATUS_ENABLED);
            $entityCreateList = [];
            foreach ($productCollection as $product) {
                $entityCreate["product_id"] = $product->getId();
                $entityCreate["store_id"] = $storeId;
                $entityCreate["product_flag"] = 1;
                $entityCreateList[] = $entityCreate;
            }
            if (count($entityCreateList) > 0) {
                $this->_connection->insertMultiple(self::VEHICLE_FITS_QUEUE, $entityCreateList);
            }
        }catch(\Exception $e){
            $this->logger->error("error in inserting products during website brand update from admin ".$e->getMessage());
        }
    }
}