<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Vehicle\Model;

use Infosys\Vehicle\Api\Data\VehicleSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Vehicle search results.
 */
class VehicleSearchResults extends SearchResults implements VehicleSearchResultsInterface
{
}
