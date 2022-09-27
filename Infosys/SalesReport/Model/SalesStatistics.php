<?php

/**
 * @package   Infosys/SalesReport
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Model;

use Infosys\SalesReport\Api\SalesStatisticsInterface;
use Magento\Framework\App\ResourceConnection;
use Infosys\SalesReport\Logger\SalesReportLogger;

/**
 * Class to calculate sales statistics
 */
class SalesStatistics implements SalesStatisticsInterface
{
    protected ResourceConnection $resource;

    protected SalesReportLogger $logger;

    /**
     * Constructor function
     *
     * @param ResourceConnection $resource
     * @param SalesReportLogger $logger
     */
    public function __construct(
        ResourceConnection $resource,
        SalesReportLogger $logger
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * Function to calculate sales statistics
     *
     * @param int $storeId
     * @param string $date
     * @return void
     */
    public function calculateSalesStatistics($storeId, $date): void
    {
        try {
            $connection = $this->resource->getConnection();
            //Calling calculateSalesStatistics stored procedure
            $sql = 'CALL calculateSalesStatistics(:store_id,:custdate)';
            $binds = ['store_id' => $storeId, 'custdate' => $date];
            $connection->query($sql, $binds);
        } catch (\Exception $e) {
            $this->logger->error("Error while calculating sales statistics " . $e);
        }
    }
}
