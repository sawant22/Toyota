<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Plugin;

use Infosys\SalesReport\Logger\SalesReportLogger;
use Magento\Sales\Model\ResourceModel\Order;
use Infosys\salesReport\Model\DealerSalesStatisticsQueueRepository;

/**
 * Class to insert data into sales statistics queue table after order save
 */
class AfterOrderSave
{
    const STATUS_COMPLETE = 'complete';

    const STATUS_CLOSED = 'closed';

    const STATUS_CANCELED = 'canceled';

    protected SalesReportLogger $logger;

    protected DealerSalesStatisticsQueueRepository $statisticsRepository;

    /**
     * Constructor function
     *
     * @param SalesReportLogger $logger
     * @param DealerSalesStatisticsQueueRepository $statisticsRepository
     */
    public function __construct(
        SalesReportLogger $logger,
        DealerSalesStatisticsQueueRepository $statisticsRepository
    ) {
        $this->logger = $logger;
        $this->statisticsRepository = $statisticsRepository;
    }

    /**
     * Function to insert data into sales statistics queue table after order save
     *
     * @param Order $subject
     * @param $result
     * @param $object
     * @return void
     */
    public function afterSave(
        Order $subject,
        $result,
        $object
    ) {
        try {
            if ($object->getStatus() == self::STATUS_COMPLETE ||
                $object->getStatus() == self::STATUS_CANCELED ||
                $object->getStatus() == self::STATUS_CLOSED
            ) {
                $this->statisticsRepository->insertDataAfterOrderSave($object);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error when inserting data into statistics queue table after order save " . $e);
        }
        return $result;
    }
}
