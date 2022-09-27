<?php

/**
 * @package     Infosys/VehicleFitment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleFitment\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Infosys\VehicleFitment\Model\FitmentCalculations;

/**
 * Indexer class for vehicle fitment calculation
 */
class FitmentIndexer implements IndexerActionInterface, MviewActionInterface
{
    protected FitmentCalculations $fitmentCalculations;

    /**
     * Constructor function
     *
     * @param FitmentCalculations $fitmentCalculations
     */
    public function __construct(
        FitmentCalculations $fitmentCalculations
    ) {
        $this->fitmentCalculations = $fitmentCalculations;
    }

    /**
     * Reindex Vehicle Fitment
     *
     * @param array $ids
     * @return void
     */
    public function reindex($ids = null): void
    {
        //calculate fitment for products
        $this->fitmentCalculations->updateProductsFitsData($ids);
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
