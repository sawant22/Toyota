<?php

/**
 * @package   Infosys/SalesReport
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Api;

/**
 * Sales statistics api interface
 */
interface SalesStatisticsInterface
{
    /**
     * Function to calculate sales statistics
     *
     * @param int $storeId
     * @param string $date
     * @return void
     */
    public function calculateSalesStatistics($storeId, $date);
}
