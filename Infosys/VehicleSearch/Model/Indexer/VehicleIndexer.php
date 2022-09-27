<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Store\Model\StoreManagerInterface;

class VehicleIndexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * Constructor function
     *
     * @param ConfigProvider $configProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }
    /**
     * Reindex Vehicle
     *
     * @param arry $ids
     * @return void
     */
    public function reindex($ids = null): void
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->configProvider->getAdapter()->reindex((int) $store->getId(), $ids);
        }
    }
    /**
     * Reindex full data
     *
     * @return void
     */
    public function executeFull(): void
    {
        $this->reindex();
    }
    /**
     * Reindex list data
     *
     * @param array $ids
     * @return void
     */
    public function executeList(array $ids): void
    {
        $this->reindex($ids);
    }
    /**
     * Reindex single data
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id): void
    {
        $this->reindex([$id]);
    }
    /**
     * Reindex array data
     *
     * @param array $ids
     * @return void
     */
    public function execute($ids): void
    {
        $this->reindex($ids);
    }
}
