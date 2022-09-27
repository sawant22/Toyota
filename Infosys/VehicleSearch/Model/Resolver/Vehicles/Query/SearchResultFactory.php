<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver\Vehicles\Query;

use Magento\Framework\ObjectManagerInterface;

/**
 * Generate SearchResult based off of total count from query and array of vehicles and their data.
 */
class SearchResultFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Instantiate SearchResult
     *
     * @param array $data
     * @return SearchResult
     */
    public function create(
        array $data
    ): SearchResult {
        return $this->objectManager->create(
            SearchResult::class,
            ['data' => $data]
        );
    }
}
