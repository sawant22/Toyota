<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver\Vehicles\Query;

use Magento\Framework\Api\Search\AggregationInterface;

/**
 * Container for a vehicle search holding the item result and the array in the GraphQL-readable vehicle type format.
 */
class SearchResult
{
    /**
     * @var $data
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Return total count of search and filtered result
     *
     * @return int
     */
    public function getTotalCount() : int
    {
        return $this->data['totalCount'] ?? 0;
    }

    /**
     * Retrieve an array in the format of GraphQL-readable type containing vehicle data.
     *
     * @return array
     */
    public function getVehiclesSearchResult() : array
    {
        return $this->data['vehiclesSearchResult'] ?? [];
    }

    /**
     * Retrieve aggregated search results
     *
     * @return AggregationInterface|null
     */
    public function getSearchAggregation(): ?AggregationInterface
    {
        return $this->data['searchAggregation'] ?? null;
    }

    /**
     * Retrieve the page size for the search
     *
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->data['pageSize'] ?? 0;
    }

    /**
     * Retrieve the current page for the search
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->data['currentPage'] ?? 0;
    }

    /**
     * Retrieve total pages for the search
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->data['totalPages'] ?? 0;
    }
}
