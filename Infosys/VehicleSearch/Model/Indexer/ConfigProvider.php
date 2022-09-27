<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Indexer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;

class ConfigProvider
{
    /**
     * Vehicle Indexer Id
     */
    const INDEXER_ID = 'vehicle_indexer';
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var array
     */
    private $adapters;
    /**
     * Constructor function
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $adapters
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $adapters = []
    ) {
        $this->objectManager = $objectManager;
        $this->adapters      = $adapters;
    }
    /**
     * Get index adapter
     *
     * @return object
     */
    public function getAdapter()
    {
        return $this->objectManager->create($this->adapters['elasticsearch7']);
    }
    /**
     * Reindex data
     *
     * @param integer $storeId
     * @param array $entityIds
     * @return void
     */
    public function reindex(int $storeId, $entityIds = null): void
    {
        $this->getAdapter()->reindex($storeId, $entityIds);
    }
}
