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
use Infosys\Vehicle\Api\Data\VehicleProductMappingInterface;
use Infosys\Vehicle\Api\VehicleProductMappingRepositoryInterface;
use Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping;
use Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping\CollectionFactory;

class VehicleProductMappingRepository implements VehicleProductMappingRepositoryInterface
{

    /**
     * @var VehicleProductMappingFactory
     */
    private $vehicleProductMappingFactory;

    /**
     * @var VehicleProductMapping
     */
    private $vehicleProductMappingResource;

    /**
     * @var VehicleMappingCollectionFactory
     */
    private $vehicleProductMappingCollectionFactory;

    /**
     * @var VehicleMappingSearchResultInterfaceFactory
     */
    private $searchResultFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * Constructor function
     *
     * @param VehicleProductMappingFactory $vehicleProductMappingFactory
     * @param VehicleProductMapping $vehicleProductMappingResource
     * @param CollectionFactory $vehicleProductMappingCollectionFactory
     * @param SearchResultsInterfaceFactory $vehicleProductMappingSearchResultInterfaceFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        VehicleProductMappingFactory $vehicleProductMappingFactory,
        VehicleProductMapping $vehicleProductMappingResource,
        CollectionFactory $vehicleProductMappingCollectionFactory,
        SearchResultsInterfaceFactory $vehicleProductMappingSearchResultInterfaceFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->vehicleProductMappingFactory = $vehicleProductMappingFactory;
        $this->vehicleProductMappingResource = $vehicleProductMappingResource;
        $this->vehicleProductMappingCollectionFactory = $vehicleProductMappingCollectionFactory;
        $this->searchResultFactory = $vehicleProductMappingSearchResultInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Get Vehicle Proudct Mapping Data
     *
     * @param int $id
     * @return \Infosys\Vehicle\Api\Data\VehicleProductMappingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $vehicleProductMapping = $this->vehicleProductMappingFactory->create();
        $this->vehicleProductResource->load($vehicleProductMapping, $id);
        if (!$vehicleProductMapping->getId()) {
            throw new NoSuchEntityException(__('Unable to find Vehicle with ID "%1"', $id));
        }
        return $vehicleProductMapping;
    }

    /**
     * Save Vehicle Proudc Mapping Data
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleProductMappingInterface $vehicleProductMapping
     * @return \Infosys\Vehicle\Api\Data\VehicleProductMappingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(VehicleProductMappingInterface $vehicleProductMapping)
    {
        $this->vehicleProductMappingResource->save($vehicleProductMapping);
        return $vehicleProductMapping;
    }

    /**
     * Delete Vehicle Proudc Mapping Data
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleProductMappingInterface $vehicleProductMapping
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(VehicleProductMappingInterface $vehicleProductMapping)
    {
        try {
            $this->vehicleProductMappingResource->delete($vehicleProductMapping);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the entry: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * Get List of Vehicle Proudc Mapping Data
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterfaceFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->vehicleProductMappingCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
