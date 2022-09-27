<?php

/**
 * @package   Infosys/DealerShippingCost
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DealerShippingCost\Api;

/**
 * Shipstation api interface
 */
interface ShipstationInterface
{
    /**
     * List shipments
     *
     * @param string $requestData
     * @param int $storeId
     * @return void
     */
    public function listShipments($requestData, $storeId);
}
