<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver\Vehicles\DataProvider\Vehicle;

use Infosys\Vehicle\Model\ResourceModel\Vehicle\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Add additional joins, attributes, and clauses to a vehicle collection.
 */
interface CollectionProcessorInterface
{
    /**
     * Process collection to add additional joins, attributes, and clauses to a vehicle collection.
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ContextInterface $context = null
    ): Collection;
}
