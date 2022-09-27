<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

use Infosys\SalesReport\Api\Data\DealerSalesStatisticsInterface;
use Infosys\SalesReport\Api\DealerSalesStatisticsRepositoryInterface;
use Infosys\SalesReport\Model\DealerSalesStatisticsFactory;
use Infosys\SalesReport\Model\ResourceModel\DealerSalesStatistics;
use Infosys\SalesReport\Model\ResourceModel\DealerSalesStatistics\CollectionFactory;

/**
 * Dealer sales statistics sepository
 */
class DealerSalesStatisticsRepository implements DealerSalesStatisticsRepositoryInterface
{

    private DealerSalesStatisticsFactory $dealerSalesStatisticsFactory;

    private DealerSalesStatistics $dealerSalesStatisticsResource;

    private CollectionFactory $dealerSalesStatisticsCollectionFactory;

    private SearchResultsInterfaceFactory $searchResultFactory;

    private CollectionProcessorInterface $collectionProcessor;
    /**
     * Constructor function
     *
     * @param DealerSalesStatisticsFactory $dealerSalesStatisticsFactory
     * @param DealerSalesStatistics $dealerSalesStatisticsResource
     * @param CollectionFactory $dealerSalesStatisticsCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        DealerSalesStatisticsFactory $dealerSalesStatisticsFactory,
        DealerSalesStatistics $dealerSalesStatisticsResource,
        CollectionFactory $dealerSalesStatisticsCollectionFactory,
        SearchResultsInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->dealerSalesStatisticsFactory = $dealerSalesStatisticsFactory;
        $this->dealerSalesStatisticsResource = $dealerSalesStatisticsResource;
        $this->dealerSalesStatisticsCollectionFactory = $dealerSalesStatisticsCollectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Get Dealer Sales Statistics Data
     *
     * @param int $id
     * @return DealerSalesStatisticsInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $dealerSalesStatistics = $this->dealerSalesStatisticsFactory->create();
        $this->dealerSalesStatisticsResource->load($dealerSalesStatistics, $id);
        if (!$dealerSalesStatistics->getId()) {
            throw new NoSuchEntityException(__('Unable to find Sales Statistics with ID "%1"', $id));
        }
        return $dealerSalesStatistics;
    }

    /**
     * Save Dealer Sales Statistics Data
     *
     * @param DealerSalesStatisticsInterface $dealerSalesStatistics
     * @return DealerSalesStatisticsInterface
     * @throws LocalizedException
     */
    public function save(DealerSalesStatisticsInterface $dealerSalesStatistics)
    {
        $this->dealerSalesStatisticsResource->save($dealerSalesStatistics);
        return $dealerSalesStatistics;
    }

    /**
     * Delete Dealer Sales Statistics Data
     *
     * @param DealerSalesStatisticsInterface $dealerSalesStatistics
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(DealerSalesStatisticsInterface $dealerSalesStatistics)
    {
        try {
            $this->dealerSalesStatisticsResource->delete($dealerSalesStatistics);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the entry: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * Get Dealer Sales Statistics Data List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterfaceFactory
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->dealerSalesStatisticsCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
