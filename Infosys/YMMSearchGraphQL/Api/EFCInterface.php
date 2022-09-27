<?php

/**
 * @package   Infosys/YMMSearchGraphQL
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\YMMSearchGraphQL\Api;

interface EFCInterface
{
    /**
     * API to get vehicle image
     *
     * @param string $year
     * @param string $trim
     * @return array
     */
    public function getVehicleImage($year, $trim);
}
