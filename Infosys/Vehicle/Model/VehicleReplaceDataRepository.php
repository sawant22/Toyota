<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface;
use Infosys\Vehicle\Api\VehicleReplaceDataRepositoryInterface;
use Infosys\Vehicle\Model\ResourceModel\VehicleReplaceData;
use Infosys\Vehicle\Model\ResourceModel\VehicleReplaceData\CollectionFactory;

class VehicleReplaceDataRepository implements VehicleReplaceDataRepositoryInterface
{

    /**
     * @var VehicleReplaceDataFactory
     */
    private $vehicleReplaceDataFactory;

    /**
     * @var VehicleReplaceData
     */
    private $vehicleReplaceDataResource;

    /**
     * @var CollectionFactory
     */
    private $vehicleReplaceDataCollectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * Constructor function
     *
     * @param VehicleReplaceDataFactory $vehicleReplaceDataFactory
     * @param VehicleReplaceData $vehicleReplaceDataResource
     * @param CollectionFactory $vehicleReplaceDataCollectionFactory
     * @param SearchResultsInterfaceFactory $vehicleSearchResultInterfaceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        VehicleReplaceDataFactory $vehicleReplaceDataFactory,
        VehicleReplaceData $vehicleReplaceDataResource,
        CollectionFactory $vehicleReplaceDataCollectionFactory,
        SearchResultsInterfaceFactory $vehicleReplaceDataSearchResultInterfaceFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->vehicleFactory = $vehicleReplaceDataFactory;
        $this->vehicleResource = $vehicleReplaceDataResource;
        $this->vehicleCollectionFactory = $vehicleReplaceDataCollectionFactory;
        $this->searchResultFactory = $vehicleReplaceDataSearchResultInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Get Vehicle Replace Data
     *
     * @param int $id
     * @return \Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $vehicle = $this->vehicleFactory->create();
        $this->vehicleResource->load($vehicle, $id);
        if (!$vehicle->getId()) {
            throw new NoSuchEntityException(__('Unable to find Vehicle replace with ID "%1"', $id));
        }
        return $vehicle;
    }

    /**
     * Save Vehicle Replace Data
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface $vehicle
     * @return \Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(VehicleReplaceDataInterface $vehicle)
    {
        $this->vehicleResource->save($vehicle);
        return $vehicle;
    }

    /**
     * Delete Vehicle Replace Data
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface $vehicle
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(VehicleReplaceDataInterface $vehicle)
    {
        try {
            $this->vehicleResource->delete($vehicle);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the entry: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * Get Vehicle Replace Data List
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterfaceFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->vehicleCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
