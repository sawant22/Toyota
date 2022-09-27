<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Cron;

use Infosys\SalesReport\Logger\SalesReportLogger;
use Infosys\SalesReport\Model\DealerSalesStatisticsQueueFactory;
use Infosys\SalesReport\Model\SalesReportStoreConfig;
use Infosys\SalesReport\Api\SalesStatisticsInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class to call sales statistics interface function for calculating statistics
 */
class CalculateDealerSalesStatistics
{
    const DEALER_SALES_STATISTICS_QUEUE_TABLE = 'toyota_dealer_sales_statistics_queue';

    protected SalesReportLogger $salesReportLogger;

    protected DealerSalesStatisticsQueueFactory $salesStatisticsQueueFactory;

    protected ResourceConnection $resource;

    protected SalesReportStoreConfig $storeConfig;

    protected SalesStatisticsInterface $salesStatisticsInterface;

    /**
     * Constructor function
     *
     * @param SalesReportLogger $salesReportLogger
     * @param DealerSalesStatisticsQueueFactory $salesStatisticsQueueFactory
     * @param ResourceConnection $resource
     * @param SalesReportStoreConfig $storeConfig
     * @param SalesStatisticsInterface $salesStatisticsInterface
     */
    public function __construct(
        SalesReportLogger $salesReportLogger,
        DealerSalesStatisticsQueueFactory $salesStatisticsQueueFactory,
        ResourceConnection $resource,
        SalesReportStoreConfig $storeConfig,
        SalesStatisticsInterface $salesStatisticsInterface
    ) {
        $this->salesReportLogger = $salesReportLogger;
        $this->salesStatisticsQueueFactory = $salesStatisticsQueueFactory;
        $this->resource = $resource;
        $this->storeConfig = $storeConfig;
        $this->salesStatisticsInterface = $salesStatisticsInterface;
    }

    /**
     * Function to call sales statistics interface function for calculating statistics
     */
    public function execute()
    {
        try {
            $maxRecords = $this->storeConfig->getMaxRecordsToCalculateReports();
            if ($maxRecords) {
                $salesStatisticsQueue = $this->salesStatisticsQueueFactory->create();
                $queueCollection = $salesStatisticsQueue->getCollection();
                $queueCollection->getSelect()->limit($maxRecords)
                    ->group(['store_id', 'report_date'])
                    ->distinct(true);
                if ($queueCollection->count()) {
                    foreach ($queueCollection as $queue) {
                        $storeId = $queue->getStoreId();
                        $reportDate = $queue->getReportDate();
                        $this->salesReportLogger->info("Calculating Statistics for the store " . $storeId .
                            " and the date " . $reportDate);

                        //Calling Sales Statistics Interface Function to Calculate Statistics & Store in the table
                        $this->salesStatisticsInterface->calculateSalesStatistics($storeId, $reportDate);

                        $connection  = $this->resource->getConnection();
                        $tableName = $connection->getTableName(self::DEALER_SALES_STATISTICS_QUEUE_TABLE);

                        $whereConditions = [
                            $connection->quoteInto('store_id = ?', $storeId),
                            $connection->quoteInto('report_date = ?', $reportDate)
                        ];
                        $connection->delete($tableName, $whereConditions);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->salesReportLogger->error('Error when calculating dealer sales statistics ' . $e);
        }
    }
}
