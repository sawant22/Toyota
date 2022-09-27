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
use Infosys\Vehicle\Api\Data\VehicleInterface;
use Infosys\Vehicle\Api\VehicleRepositoryInterface;
use Infosys\Vehicle\Model\ResourceModel\Vehicle;
use Infosys\Vehicle\Model\ResourceModel\Vehicle\CollectionFactory;

class VehicleRepository implements VehicleRepositoryInterface
{

    /**
     * @var VehicleFactory
     */
    private $vehicleFactory;

    /**
     * @var Vehicle
     */
    private $vehicleResource;

    /**
     * @var VehicleCollectionFactory
     */
    private $vehicleCollectionFactory;

    /**
     * @var VehicleSearchResultInterfaceFactory
     */
    private $searchResultFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * Constructor function
     *
     * @param VehicleFactory $vehicleFactory
     * @param Vehicle $vehicleResource
     * @param CollectionFactory $vehicleCollectionFactory
     * @param SearchResultsInterfaceFactory $vehicleSearchResultInterfaceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        VehicleFactory $vehicleFactory,
        Vehicle $vehicleResource,
        CollectionFactory $vehicleCollectionFactory,
        SearchResultsInterfaceFactory $vehicleSearchResultInterfaceFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->vehicleFactory = $vehicleFactory;
        $this->vehicleResource = $vehicleResource;
        $this->vehicleCollectionFactory = $vehicleCollectionFactory;
        $this->searchResultFactory = $vehicleSearchResultInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Get Vehicle Data
     *
     * @param int $id
     * @return \Infosys\Vehicle\Api\Data\VehicleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $vehicle = $this->vehicleFactory->create();
        $this->vehicleResource->load($vehicle, $id);
        if (!$vehicle->getId()) {
            throw new NoSuchEntityException(__('Unable to find Vehicle with ID "%1"', $id));
        }
        return $vehicle;
    }

    /**
     * Save Vehicle Data
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleInterface $vehicle
     * @return \Infosys\Vehicle\Api\Data\VehicleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(VehicleInterface $vehicle)
    {
        $this->vehicleResource->save($vehicle);
        return $vehicle;
    }

    /**
     * Delete Vehicle Data
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleInterface $vehicle
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(VehicleInterface $vehicle)
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
     * Get Vehicle Data List
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
