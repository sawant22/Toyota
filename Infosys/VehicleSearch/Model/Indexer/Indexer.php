<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Infosys\VehicleSearch\Model\Indexer\Fulltext\Action\Full;
use Infosys\Vehicle\Logger\VehicleLogger;
use Magento\Framework\App\ResourceConnection;

/**
 * Indexer class for vehicle search
 */
class Indexer
{
    const DEFAULT_BATCH_SIZE = 500;
    
    const INDEX_NAME = 'vehicle_indexer_index';

    private ElasticsearchAdapter $adapter;
  
    private Batch $batch;
   
    private Full $fullAction;

    private $batchSize;

    protected VehicleLogger $logger;

    /**
     * Constructor function
     *
     * @param ElasticsearchAdapter $adapter
     * @param Batch $batch
     * @param Full $fullAction
     * @param ResourceConnection $resource
     * @param VehicleLogger $logger
     * @param interger $batchSize
     */
    public function __construct(
        ElasticsearchAdapter $adapter,
        Batch $batch,
        Full $fullAction,
        ResourceConnection $resource,
        VehicleLogger $logger,
        $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->adapter = $adapter;
        $this->batch = $batch;
        $this->fullAction = $fullAction;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->logger = $logger;
        $this->batchSize = $batchSize;
    }

    /**
     * Reindex data
     *
     * @param integer $storeId
     * @param array $entityIds
     * @return void
     */
    public function reindex(int $storeId, $entityIds): void
    {
        //log entity ids on single vehicle index
        $this->logger->info("Entity ids : ".json_encode($entityIds));

        if (null === $entityIds) {
            $this->adapter->cleanIndex($storeId, self::INDEX_NAME);
            $this->saveIndexes($storeId, $entityIds);
        } else {
            $this->deleteIndexes($storeId, $entityIds);
            $this->saveIndexes($storeId, $entityIds);
        }

        //add mapped product indexes
        if ($entityIds && $storeId == 1) {
            $this->logger->info("Adding product ids into catalog search : ".json_encode($entityIds));
            $this->addProductIndexes($entityIds);
        }
    }

    /**
     * Delete indexes
     *
     * @param integer $storeId
     * @param array $entityIds
     * @return void
     */
    protected function deleteIndexes($storeId, $entityIds)
    {
        $entityIdsArr = new \ArrayIterator($entityIds);
        $documentIds = [];
        foreach ($entityIdsArr as $entity) {
            $documentIds[$entity] = $entity;
        }
        $this->adapter->deleteDocs($documentIds, $storeId, self::INDEX_NAME);
    }

    /**
     * Save indexes
     *
     * @param integer $storeId
     * @param array $entityIds
     * @return void
     */
    protected function saveIndexes($storeId, $entityIds)
    {
        $documents =  $this->fullAction->rebuildIndex($entityIds);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->adapter->prepareDocsPerStore($documentsBatch, $storeId);
            $this->adapter->addDocs($docs, $storeId, self::INDEX_NAME);
        }

        $this->adapter->checkIndex($storeId, self::INDEX_NAME, false);
        $this->adapter->updateAlias($storeId, self::INDEX_NAME);
    }

    /**
     * Add catalog search index for mapped products
     *
     * @param array $entityIds
     * @return void
     */
    protected function addProductIndexes($entityIds)
    {
        $entityIds = implode(',', $entityIds);
        $bulkInsert = [];
        
        //fetch mapped product ids
        $select = $this->_connection->select()->from(
            'catalog_vehicle_product',
            ['product_id']
        )->where('vehicle_id IN (?)', $entityIds);
        $mappedProducts = $this->_connection->fetchCol($select);

        //add product indexes
        if ($mappedProducts) {
            foreach ($mappedProducts as $product) {
                $bulkInsert[] = [
                    'entity_id' => $product
                ];
            }
            $this->_connection->insertMultiple('catalogsearch_fulltext_cl', $bulkInsert);
        }
    }
}
