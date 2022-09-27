<?php

/**
 * @package     Infosys/DealerShippingCost
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DealerShippingCost\Model;

use Infosys\DirectFulFillment\Model\FreightRecoveryFactory;
use Infosys\DealerShippingCost\Model\Shipstation;
use Infosys\DealerShippingCost\Logger\ShippingCostLogger;

/**
 * Class to handle shipstation shipping cost
 */
class ShipstationShippingCost
{
    protected FreightRecoveryFactory $freightRecoveryFactory;

    protected Shipstation $shipstation;

    protected ShippingCostLogger $logger;

    /**
     * Constructor function
     *
     * @param FreightRecoveryFactory $freightRecoveryFactory
     * @param Shipstation $shipstation
     * @param ShippingCostLogger $logger
     */
    public function __construct(
        FreightRecoveryFactory $freightRecoveryFactory,
        Shipstation $shipstation,
        ShippingCostLogger $logger
    ) {
        $this->freightRecoveryFactory = $freightRecoveryFactory;
        $this->shipstation = $shipstation;
        $this->logger = $logger;
    }

    /**
     * Method to update shipstation shipping cost in df sales order table
     *
     * @param object $shipment
     * @param int $storeId
     * @return void
     */
    public function execute($shipment, $storeId): void
    {
        try {
            $orderNumber = $shipment->getOrder()->getIncrementId();
            $trackingNumber = $shipment->getTracksCollection()->getFirstItem()->getTrackNumber();
            $this->logger->info("Current shipment tracking no " . $trackingNumber);

            $shipmentList = $this->shipstation->listShipments($orderNumber, $storeId);
            if (isset($shipmentList['shipments'])) {
                foreach ($shipmentList['shipments'] as $ship) {
                    $this->logger->info("Shipstation tracking number " . $ship['trackingNumber']);
                    if ($ship['trackingNumber'] == $trackingNumber) {
                        $shippingCost = $ship['shipmentCost'];
                        $this->logger->info("Shipstation shipment cost " . $shippingCost);
                        $freightRecovery = $this->freightRecoveryFactory->create();
                        $freightRecovery->setFreightRecovery($shippingCost);
                        $freightRecovery->setAction('shipstation');
                        $freightRecovery->setOrderId($shipment->getOrder()->getId());
                        $freightRecovery->setCreatedAt(date_default_timezone_get());
                        $freightRecovery->setShipmentId($shipment->getId());
                        $freightRecovery->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("Error in Shipstation Shipping Cost update" . $e);
        }
    }
}
