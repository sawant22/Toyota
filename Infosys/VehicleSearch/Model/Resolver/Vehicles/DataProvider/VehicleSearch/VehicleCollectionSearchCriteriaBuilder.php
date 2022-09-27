<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver\Vehicles\DataProvider\VehicleSearch;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Builds a search criteria intended for the vehicle collection based on search criteria used on SearchAPI
 */
class VehicleCollectionSearchCriteriaBuilder
{
    /** @var SearchCriteriaInterfaceFactory */
    private $searchCriteriaFactory;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var FilterGroupBuilder */
    private $filterGroupBuilder;

    /**
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * Build searchCriteria from search for vehicle collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     */
    public function build(SearchCriteriaInterface $searchCriteria): SearchCriteriaInterface
    {
        //Create a copy of search criteria without filters to preserve the results from search
        $searchCriteriaForCollection = $this->searchCriteriaFactory->create()
            ->setSortOrders($searchCriteria->getSortOrders())
            ->setPageSize($searchCriteria->getPageSize())
            ->setCurrentPage($searchCriteria->getCurrentPage());

        return $searchCriteriaForCollection;
    }
}
