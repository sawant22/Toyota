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
use Infosys\DealerShippingCost\Logger\ShippingCostLogger;

/**
 * Class to handle manual shipping cost
 */
class ManualShippingCost
{
    protected FreightRecoveryFactory $freightRecoveryFactory;

    protected ShippingCostLogger $shippingCostLogger;

    /**
     * Constructor function
     *
     * @param FreightRecoveryFactory $freightRecoveryFactory
     * @param ShippingCostLogger $shippingCostLogger
     */
    public function __construct(
        FreightRecoveryFactory $freightRecoveryFactory,
        ShippingCostLogger $shippingCostLogger
    ) {
        $this->freightRecoveryFactory = $freightRecoveryFactory;
        $this->shippingCostLogger = $shippingCostLogger;
    }

    /**
     * Method to update manual dealer shipping cost in df sales order table
     *
     * @param float $shippingCost
     * @param object $shipment
     * @return void
     */
    public function execute($shippingCost, $shipment): void
    {
        try {
            $freightRecovery = $this->freightRecoveryFactory->create();
            $freightRecovery->setOrderId($shipment->getOrder()->getId());
            $freightRecovery->setFreightRecovery($shippingCost);
            $freightRecovery->setCreatedAt(date_default_timezone_get());
            $freightRecovery->setAction('manual');
            $freightRecovery->setShipmentId($shipment->getId());
            $freightRecovery->save();
        } catch (\Exception $e) {
            $this->shippingCostLogger->error('Error in Manual Shipping Cost update' . $e);
        }
    }
}
