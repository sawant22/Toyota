<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\VehicleSearch\Model\ResourceModel\Vehicle\Indexer;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;
use Infosys\VehicleSearch\Model\ResourceModel\Vehicle\Indexer\AbstractIndexer;

class Fulltext extends AbstractIndexer
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * Constructor function
     *
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct($resource, $storeManager);
    }

    /**
     * Load a bulk of vehicle data.
     *
     * @param string  $entityIds
     * @param integer $fromId
     * @param integer $limit
     *
     * @return array
     */
    public function getSearchableVehicle($entityIds = null, $fromId = 0, $limit = 100)
    {
        $select = $this->getConnection()->select()
            ->from(['p' => $this->getTable('catalog_vehicle_entity')]);

        if ($entityIds !== null && !empty($entityIds)) {
            $select->where('p.entity_id IN (?)', $entityIds);
        }

        $select->where('p.entity_id > ?', $fromId)
            ->where('p.status = ?', 1)
            ->limit($limit)
            ->order('p.entity_id');

        return $this->connection->fetchAll($select);
    }
}
