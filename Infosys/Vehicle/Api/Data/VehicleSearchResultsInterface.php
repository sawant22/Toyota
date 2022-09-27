<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface VehicleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Infosys\Vehicle\Api\Data\VehicleInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
