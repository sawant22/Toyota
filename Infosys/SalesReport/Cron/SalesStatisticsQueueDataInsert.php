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
use Magento\Store\Model\StoreManagerInterface;
use Infosys\SalesReport\Model\DealerSalesStatisticsQueueFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class to insert store id and date into toyota sales statistics queue table
 */
class SalesStatisticsQueueDataInsert
{
    const SALES_STATISTICS_QUEUE_TABLE = 'toyota_dealer_sales_statistics_queue';

    protected StoreManagerInterface $storeManager;

    protected SalesReportLogger $salesReportLogger;

    /**
     * Constructor function
     *
     * @param StoreManagerInterface $storeManager
     * @param SalesReportLogger $salesReportLogger
     * @param DealerSalesStatisticsQueueFactory $salesStatisticsQueueFactory
     * @param ResourceConnection $resource
     * @param DateTime $date
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SalesReportLogger $salesReportLogger,
        DealerSalesStatisticsQueueFactory $salesStatisticsQueueFactory,
        ResourceConnection $resource,
        DateTime $date
    ) {
        $this->storeManager = $storeManager;
        $this->salesReportLogger = $salesReportLogger;
        $this->salesStatisticsQueueFactory = $salesStatisticsQueueFactory;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->date = $date;
    }

    /**
     * Function to insert store id and date into toyota sales statistics queue table
     */
    public function execute()
    {
        try {
            $storeIds = array_keys($this->storeManager->getStores());
            $date = $this->date->date('Y-m-d');
            $yesterdayDate = $this->date->date('Y-m-d', strtotime($date . " -1 days"));
            $this->salesReportLogger->info("Yesterday's Date " . $yesterdayDate);
            foreach ($storeIds as $storeId) {
                $entityCreateList[] = [
                    'store_id' => $storeId,
                    'report_date' => $yesterdayDate
                ];
            }
            if (count($entityCreateList) > 0) {
                $this->connection->insertMultiple(self::SALES_STATISTICS_QUEUE_TABLE, $entityCreateList);
            }
        } catch (\Exception $e) {
            $this->salesReportLogger->error('Error when inserting data into sales statistics queue table ' . $e);
        }
    }
}
