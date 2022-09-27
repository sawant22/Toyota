<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\VehicleSearch\Model\ResourceModel\Vehicle\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class AbstractIndexer
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor.
     *
     * @param ResourceConnection    $resource     Database adpater.
     * @param StoreManagerInterface $storeManager Store manager.
     */
    public function __construct(ResourceConnection $resource, StoreManagerInterface $storeManager)
    {
        $this->resource     = $resource;
        $this->connection   = $resource->getConnection();
        $this->storeManager = $storeManager;
    }

    /**
     * Get table name using the adapter.
     *
     * @param string $tableName Table name.
     *
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName);
    }

    /**
     * Return database connection.
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get store by id.
     *
     * @param integer $storeId Store id.
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    protected function getStore($storeId)
    {
        return $this->storeManager->getStore($storeId);
    }
}
