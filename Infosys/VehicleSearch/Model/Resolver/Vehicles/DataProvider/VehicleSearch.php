<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver\Vehicles\DataProvider;

use Infosys\Vehicle\Api\Data\VehicleSearchResultsInterfaceFactory;
use Infosys\Vehicle\Model\ResourceModel\Vehicle\Collection;
use Infosys\Vehicle\Model\ResourceModel\Vehicle\CollectionFactory;
use Infosys\VehicleSearch\Model\Resolver\Vehicles\DataProvider\Vehicle\CollectionProcessorInterface;
use Infosys\VehicleSearch\Model\Resolver\Vehicles\DataProvider\VehicleSearch\VehicleCollectionSearchCriteriaBuilder;
use Infosys\VehicleSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Infosys\VehicleSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Vehicle field data provider for vehicle search, used for GraphQL resolver processing.
 */
class VehicleSearch
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var VehicleCollectionSearchCriteriaBuilder
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionPreProcessor;

    /**
     * @var VehicleCollectionSearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchResultApplierFactory;
     */
    private $searchResultApplierFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param VehicleSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionPreProcessor
     * @param VehicleCollectionSearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultApplierFactory $searchResultsApplierFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        VehicleSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionPreProcessor,
        VehicleCollectionSearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultApplierFactory $searchResultsApplierFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionPreProcessor = $collectionPreProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultApplierFactory = $searchResultsApplierFactory;
    }

    /**
     * Get list of vehicle data with full data set. Adds eav attributes to result set from passed in array
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param SearchResultInterface $searchResult
     * @param array $attributes
     * @param ContextInterface|null $context
     * @return SearchResultsInterface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria,
        SearchResultInterface $searchResult,
        array $attributes = [],
        ContextInterface $context = null
    ): SearchResultsInterface {
       
        $collection = $this->collectionFactory->create();

        //Create a copy of search criteria without filters to preserve the results from search
        $searchCriteriaForCollection = $this->searchCriteriaBuilder->build($searchCriteria);

        //Apply Vehicle search results from search and join table
        $this->getSearchResultsApplier(
            $searchResult,
            $collection,
            $this->getSortOrderArray($searchCriteriaForCollection)
        )->apply();
        
        $this->collectionPreProcessor->process($collection, $searchCriteriaForCollection, $attributes, $context);
        $collection->load();
       
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Create searchResultApplier
     *
     * @param SearchResultInterface $searchResult
     * @param Collection $collection
     * @param array $orders
     * @return SearchResultApplierInterface
     */
    private function getSearchResultsApplier(
        SearchResultInterface $searchResult,
        Collection $collection,
        array $orders
    ): SearchResultApplierInterface {
        return $this->searchResultApplierFactory->create(
            [
                'collection' => $collection,
                'searchResult' => $searchResult,
                'orders' => $orders
            ]
        );
    }

    /**
     * Format sort orders into associative array
     *
     * E.g. ['field1' => 'DESC', 'field2' => 'ASC", ...]
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    private function getSortOrderArray(SearchCriteriaInterface $searchCriteria)
    {
        $ordersArray = [];
        $sortOrders = $searchCriteria->getSortOrders();
        if (is_array($sortOrders)) {
            foreach ($sortOrders as $sortOrder) {
                if ($sortOrder->getField() === '_id') {
                    $sortOrder->setField('entity_id');
                }
                $ordersArray[$sortOrder->getField()] = $sortOrder->getDirection();
            }
        }

        return $ordersArray;
    }
}
