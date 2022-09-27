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

use Infosys\SalesReport\Api\Data\DealerSalesStatisticsQueueInterface;
use Infosys\SalesReport\Api\DealerSalesStatisticsQueueRepositoryInterface;
use Infosys\SalesReport\Model\DealerSalesStatisticsQueueFactory;
use Infosys\SalesReport\Model\ResourceModel\DealerSalesStatisticsQueue;
use Infosys\SalesReport\Model\ResourceModel\DealerSalesStatisticsQueue\CollectionFactory;
use Infosys\SalesReport\Logger\SalesReportLogger;

/**
 * Toyota dealer sales statistics queue repository
 */
class DealerSalesStatisticsQueueRepository implements DealerSalesStatisticsQueueRepositoryInterface
{

    private DealerSalesStatisticsQueueFactory $dealerSalesStatisticsQueueFactory;

    private DealerSalesStatisticsQueue $dealerSalesStatisticsQueueResource;

    private CollectionFactory $dealerSalesStatisticsQueueCollectionFactory;

    private SearchResultsInterfaceFactory $searchResultFactory;

    private CollectionProcessorInterface $collectionProcessor;

    private SalesReportLogger $logger;

    /**
     * Constructor function
     *
     * @param DealerSalesStatisticsQueueFactory $dealerSalesStatisticsQueueFactory
     * @param DealerSalesStatisticsQueue $dealerSalesStatisticsQueueResource
     * @param CollectionFactory $dealerSalesStatisticsQueueCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SalesReportLogger $logger
     */
    public function __construct(
        DealerSalesStatisticsQueueFactory $dealerSalesStatisticsQueueFactory,
        DealerSalesStatisticsQueue $dealerSalesStatisticsQueueResource,
        CollectionFactory $dealerSalesStatisticsQueueCollectionFactory,
        SearchResultsInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        SalesReportLogger $logger
    ) {
        $this->dealerSalesStatisticsQueueFactory = $dealerSalesStatisticsQueueFactory;
        $this->dealerSalesStatisticsQueueResource = $dealerSalesStatisticsQueueResource;
        $this->dealerSalesStatisticsQueueCollectionFactory = $dealerSalesStatisticsQueueCollectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->logger = $logger;
    }

    /**
     * Get dealer sales statistics queue data by id
     *
     * @param int $id
     * @return DealerSalesStatisticsQueueInterface
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $dealerSalesStatisticsQueue = $this->dealerSalesStatisticsQueueFactory->create();
        $this->dealerSalesStatisticsQueueResource->load($dealerSalesStatisticsQueue, $id);
        if (!$dealerSalesStatisticsQueue->getId()) {
            throw new NoSuchEntityException(__('Unable to find Sales Statistics Queue with ID "%1"', $id));
        }
        return $dealerSalesStatisticsQueue;
    }

    /**
     * Save dealer sales statistics queue data
     *
     * @param DealerSalesStatisticsQueueInterface $dealerSalesStatisticsQueue
     * @return DealerSalesStatisticsInterface
     * @throws LocalizedException
     */
    public function save(DealerSalesStatisticsQueueInterface $dealerSalesStatisticsQueue)
    {
        $this->dealerSalesStatisticsQueueResource->save($dealerSalesStatisticsQueue);
        return $dealerSalesStatisticsQueue;
    }

    /**
     * Delete sealer sales statistics queue data
     *
     * @param DealerSalesStatisticsQueueInterface $dealerSalesStatisticsQueue
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(DealerSalesStatisticsQueueInterface $dealerSalesStatisticsQueue)
    {
        try {
            $this->dealerSalesStatisticsQueueResource->delete($dealerSalesStatisticsQueue);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the entry: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * Get dealer sales statistics queue data list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterfaceFactory
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->dealerSalesStatisticsQueueCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * Function to insert store id and date into sales statistics queue table
     *
     * @param object $order
     * @return void
     */
    public function insertDataAfterOrderSave($order): void
    {
        try {
            $orderDate = date('Y-m-d', strtotime($order->getCreatedAt()));
            $dealerSalesStatisticsQueue = $this->dealerSalesStatisticsQueueFactory->create();
            $dealerSalesStatisticsQueue->setStoreId($order->getStore()->getId());
            $dealerSalesStatisticsQueue->setReportDate($orderDate);
            $dealerSalesStatisticsQueue->save();
        } catch (\Exception $e) {
            $this->logger->error("Error when inserting data into queue table after order save " . $e);
        }
    }
}
