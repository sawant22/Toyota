<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Api;

use Infosys\SalesReport\Api\Data\DealerSalesStatisticsQueueInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Toyota dealer sales statistics queue repository interface
 */
interface DealerSalesStatisticsQueueRepositoryInterface
{
    /**
     * Create Dealer Sales Statistics Queue
     *
     * @param DealerSalesStatisticsQueueInterface $dealerSalesStatisticsQueue
     * @return DealerSalesStatisticsQueueInterface
     * @throws InputException
     * @throws StateException
     * @throws CouldNotSaveException
     */
    public function save(DealerSalesStatisticsQueueInterface $dealerSalesStatisticsQueue);

    /**
     * Get info about Dealer Sales Statistics Queue by id
     *
     * @param int $id
     * @return DealerSalesStatisticsQueueInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete Dealer Sales Statistics Queue
     *
     * @param DealerSalesStatisticsQueueInterface $dealerSalesStatisticsQueue
     * @return bool Will returned True if deleted
     * @throws StateException
     */
    public function delete(DealerSalesStatisticsQueueInterface $dealerSalesStatisticsQueue);

    /**
     * Get Dealer Sales Statistics Queue list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
