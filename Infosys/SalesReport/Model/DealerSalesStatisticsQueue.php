<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Infosys\SalesReport\Api\Data\DealerSalesStatisticsQueueInterface;

/**
 * Model class for toyota dealer sales statistics queue table
 */
class DealerSalesStatisticsQueue extends AbstractExtensibleModel implements DealerSalesStatisticsQueueInterface
{
    /**
     * Initialize Resource Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\DealerSalesStatisticsQueue::class);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return parent::getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getReportDate()
    {
        return parent::getData(self::DATE);
    }

    /**
     * @inheritDoc
     */
    public function setReportDate($date)
    {
        return $this->setData(self::DATE, $date);
    }
}
