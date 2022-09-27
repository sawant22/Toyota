<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\VehicleSearch\Model\ResourceModel\Fulltext\Collection;

/**
 * Resolve specific attributes for search criteria.
 */
interface SearchResultApplierInterface
{
    /**
     * Apply search results to collection.
     *
     * @return void
     */
    public function apply();
}
