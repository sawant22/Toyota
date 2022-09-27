<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Indexer;

class ElasticsearchAdapter
{
    /**
     * @var Indexer
     */
    private $indexer;
    /**
     * Constructor function
     *
     * @param Indexer $indexer
     */
    public function __construct(
        Indexer $indexer
    ) {
        $this->indexer = $indexer;
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
        $this->indexer->reindex($storeId, $entityIds);
    }
}
