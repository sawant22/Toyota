<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface VehicleProductMappingInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const VEHICLE_PRODUCT_MAPPING_TABLE = 'catalog_vehicle_product';

    const ID = 'entity_id';

    const PRODUCT_ID = 'product_id';

    const VEHICLE_ID = 'vehicle_id';

    /**#@-*/

    /**
     * Vehicle id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set vehicle id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Vehicle Product Id
     *
     * @return string
     */
    public function getProductId();

    /**
     * Set vehicle Product Id
     *
     * @param string $title
     * @return $this
     */
    public function setProductId($title);

    /**
     * Vehicle Id
     *
     * @return string|null
     */
    public function getVehicleId();

    /**
     * Set vehicle Id
     *
     * @param string $brand
     * @return $this
     */
    public function setVehicleId($brand);
}
