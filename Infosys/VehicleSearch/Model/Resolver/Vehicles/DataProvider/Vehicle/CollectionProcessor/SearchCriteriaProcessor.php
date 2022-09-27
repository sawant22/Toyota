<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver\Vehicles\DataProvider\Vehicle\CollectionProcessor;

use Infosys\Vehicle\Model\ResourceModel\Vehicle\Collection;
use Infosys\VehicleSearch\Model\Resolver\Vehicles\DataProvider\Vehicle\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface as SearchCriteriaApplier;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Apply search criteria data to passed in collection.
 *
 * {@inheritdoc}
 */
class SearchCriteriaProcessor implements CollectionProcessorInterface
{
    /**
     * @var SearchCriteriaApplier
     */
    private $searchCriteriaApplier;

    /**
     * @param SearchCriteriaApplier $searchCriteriaApplier
     */
    public function __construct(SearchCriteriaApplier $searchCriteriaApplier)
    {
        $this->searchCriteriaApplier = $searchCriteriaApplier;
    }

    /**
     * Process collection to add additional joins, attributes, and clauses to a vehicle collection.
     *
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface|null $context
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ContextInterface $context = null
    ): Collection {
        $this->searchCriteriaApplier->process($searchCriteria, $collection);

        return $collection;
    }
}
