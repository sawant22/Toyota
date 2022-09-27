<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Toyota dealer sales statistics queue interface
 */
interface DealerSalesStatisticsQueueInterface extends ExtensibleDataInterface
{

    const TOYOTA_DEALER_SALES_STATISTICS_QUEUE_TABLE = 'toyota_dealer_sales_statistics_queue';

    const ID = 'entity_id';

    const STORE_ID = 'store_id';

    const DATE = 'report_date';

    /**
     * Entity id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set entity id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set Store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get sales statistics queue date
     *
     * @return string
     */
    public function getReportDate();

    /**
     * Set sales statistics queue date
     *
     * @param string $date
     * @return $this
     */
    public function setReportDate($date);
}
