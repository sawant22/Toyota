<?php

/**
 * @package Infosys/CreateWebsite
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CreateWebsite\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
/**
 * @api
 * @since 100.0.2
 */
interface TRDInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const TRD_TABLE = 'toyota_dealer_regions';

    const ID = 'id';

    const REGION_CODE = 'region_code';

    const REGION_LABEL = 'region_label';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    /**#@-*/

    /**
     * TRD id
     *
     * @return string|null
     */
    public function getRegionLabel();

    /**
     * Set trd id
     *
     * @param int $id
     * @return $this
     */
    public function setRegionLabel($id);

    /**
     * TRD code
     *
     * @return int|null
     */
    public function getRegionCode();

    /**
     * Set trd code
     *
     * @param int $code
     * @return $this
     */
    public function setRegionCode($code);

    /**
     * trd created date
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set trd created date
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * trd updated date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set trd updated date
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
