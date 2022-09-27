<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Api;

use Infosys\SalesReport\Api\Data\DealerSalesStatisticsInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Toyota dealer sales statistics repository interface
 */
interface DealerSalesStatisticsRepositoryInterface
{
    /**
     * Create Dealer Sales Statistics
     *
     * @param DealerSalesStatisticsInterface $dealerSalesStatistics
     * @return DealerSalesStatisticsInterface
     * @throws InputException
     * @throws StateException
     * @throws CouldNotSaveException
     */
    public function save(DealerSalesStatisticsInterface $dealerSalesStatistics);

    /**
     * Get info about Dealer Sales Statistics by id
     *
     * @param int $id
     * @return DealerSalesStatisticsInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete Dealer Sales Statistics
     *
     * @param DealerSalesStatisticsInterface $dealerSalesStatistics
     * @return bool Will returned True if deleted
     * @throws StateException
     */
    public function delete(DealerSalesStatisticsInterface $dealerSalesStatistics);

    /**
     * Get Dealer Sales Statistics list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
