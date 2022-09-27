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
interface VehicleReplaceDataInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const VEHICLE_TABLE = 'vehicle_data_replace';

    const ID = 'entity_id';

    const ATTRIBUTE = 'attribute';

    const FIND = 'find';

    const REPLACE = 'replace';

    /**#@-*/

    /**
     * Get Vehicle Replace data id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Vehicle Replace data id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Vehicle Attribute
     *
     * @return string
     */
    public function getAttribute();

    /**
     * Set Vehicle Attribute
     *
     * @param string $attribute
     * @return $this
     */
    public function setAttribute($attribute);

    /**
     * Get Vehicle Attribute find Value
     *
     * @return string|null
     */
    public function getFind();

    /**
     * Set Vehicle Attribute find Value
     *
     * @param string $find
     * @return $this
     */
    public function setFind($find);

    /**
     * Set Vehicle Attribute Replace Value
     *
     * @return string|null
     */
    public function getReplace();

    /**
     * Set Vehicle Attribute Replace Value
     *
     * @param string $replace
     * @return $this
     */
    public function setReplace($replace);
   
}
