<?php

/**
 * @package Infosys/CreateWebsite
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CreateWebsite\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Infosys\CreateWebsite\Api\Data\TRDInterface;
use Infosys\CreateWebsite\Api\TRDRepositoryInterface;
use Infosys\CreateWebsite\Model\ResourceModel\TRD;
use Infosys\CreateWebsite\Model\ResourceModel\TRD\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

class TRDRepository implements TRDRepositoryInterface
{

    /**
     * @var TRDFactory
     */
    private $trdFactory;

    /**
     * @var TRD
     */
    private $trdResource;

    /**
     * @var TRDCollectionFactory
     */
    private $trdCollectionFactory;

    /**
     * @var TRDSearchResultInterfaceFactory
     */
    private $searchResultFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * Constructor function
     *
     * @param TRDFactory $trdFactory
     * @param TRD $trdResource
     * @param CollectionFactory $trdCollectionFactory
     * @param SearchResultsInterfaceFactory $trdSearchResultInterfaceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        TRDFactory $trdFactory,
        TRD $trdResource,
        CollectionFactory $trdCollectionFactory,
        SearchResultsInterfaceFactory $trdSearchResultInterfaceFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->trdFactory = $trdFactory;
        $this->trdResource = $trdResource;
        $this->trdCollectionFactory = $trdCollectionFactory;
        $this->searchResultFactory = $trdSearchResultInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Get TRD Data
     *
     * @param int $id
     * @return TRDInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $trd = $this->trdFactory->create();
        $this->trdResource->load($trd, $id);
        if (!$trd->getId()) {
            throw new NoSuchEntityException(__('Unable to find Region with ID "%1"', $id));
        }
        return $trd;
    }

    /**
     * Save TRD Data
     *
     * @param TRDInterface $trd
     * @return TRDInterface
     * @throws LocalizedException
     */
    public function save(TRDInterface $trd)
    {
        $this->trdResource->save($trd);
        return $trd;
    }

    /**
     * Delete TRD Data
     *
     * @param TRDInterface $trd
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(TRDInterface $trd)
    {
        try {
            $this->trdResource->delete($trd);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the entry: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * Get TRD Data List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterfaceFactory
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->trdCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
