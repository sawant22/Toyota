<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver\Vehicles\Query;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Build search criteria
 */
class SearchCriteriaBuilder
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param Builder $builder
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        Builder $builder,
        ScopeConfigInterface $scopeConfig,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->builder = $builder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Build search criteria
     *
     * @param array $args
     * @param bool $includeAggregation
     * @return SearchCriteriaInterface
     */
    public function build(array $args, bool $includeAggregation): SearchCriteriaInterface
    {
        $searchCriteria = $this->builder->build('products', $args);
        $isSearch = !empty($args['search']);
     
        $requestName = 'graphql_vehicle_search_with_aggregation';
        $searchCriteria->setRequestName($requestName);

        if ($isSearch) {
            $this->addFilter($searchCriteria, 'search_term', $args['search']);
        }

        if (!$searchCriteria->getSortOrders()) {
            $this->addDefaultSortOrder($searchCriteria, $args, $isSearch);
        }

        if (empty($args['filter'])) {
            $this->addFilter($searchCriteria, 'status', 1);
        }

        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);

        return $searchCriteria;
    }

    /**
     * Add sort by Entity ID
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param bool $isSearch
     */
    private function addEntityIdSort(SearchCriteriaInterface $searchCriteria, bool $isSearch): void
    {
        if ($isSearch) {
            return;
        }
        $sortOrderArray = $searchCriteria->getSortOrders();
        $sortOrderArray[] = $this->sortOrderBuilder
            ->setField('_id')
            ->setDirection(SortOrder::SORT_DESC)
            ->create();
        $searchCriteria->setSortOrders($sortOrderArray);
    }

    /**
     * Add filter to search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param string $field
     * @param mixed $value
     * @param string|null $condition
     */
    private function addFilter(
        SearchCriteriaInterface $searchCriteria,
        string $field,
        $value,
        ?string $condition = null
    ): void {
        $filter = $this->filterBuilder
            ->setField($field)
            ->setValue($value)
            ->setConditionType($condition)
            ->create();

        $this->filterGroupBuilder->addFilter($filter);
        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups[] = $this->filterGroupBuilder->create();
        $searchCriteria->setFilterGroups($filterGroups);
    }

    /**
     * Sort by relevance DESC by default
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $args
     * @param bool $isSearch
     */
    private function addDefaultSortOrder(SearchCriteriaInterface $searchCriteria, array $args, $isSearch = false): void
    {
        $defaultSortOrder = [];
        if ($isSearch) {
            $defaultSortOrder[] = $this->sortOrderBuilder
                ->setField('relevance')
                ->setDirection(SortOrder::SORT_DESC)
                ->create();
        } else {
            if (empty($args['filter'])) {
                $defaultSortOrder[] = $this->sortOrderBuilder
                    ->setField('model_year')
                    ->setDirection(SortOrder::SORT_ASC)
                    ->create();
            }
        }
        
        $searchCriteria->setSortOrders($defaultSortOrder);
    }
}
