<?php

/**
 * @package Infosys/CreateWebsite
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CreateWebsite\Api;

use Infosys\CreateWebsite\Api\Data\TRDInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
/**
 * @api
 * @since 100.0.2
 */
interface TRDRepositoryInterface
{
    /**
     * Create trd
     *
     * @param TRDInterface $trd
     * @return TRDInterface
     * @throws InputException
     * @throws StateException
     * @throws CouldNotSaveException
     */
    public function save(TRDInterface $trd);

    /**
     * Get info about trd by trd id
     *
     * @param int $trdId
     * @return TRDInterface
     * @throws NoSuchEntityException
     */
    public function getById($trdId);

    /**
     * Delete trd
     *
     * @param TRDInterface $trd
     * @return bool Will returned True if deleted
     * @throws StateException
     */
    public function delete(TRDInterface $trd);

    /**
     * Get trd list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
