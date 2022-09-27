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
interface VehicleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const VEHICLE_TABLE = 'catalog_vehicle_entity';

    const ID = 'entity_id';

    const TITLE = 'title';

    const BRAND = 'brand';

    const MODEL_YEAR = 'model_year';

    const MODEL_CODE = 'model_code';

    const SERIES_NAME = 'series_name';

    const GRADE = 'grade';

    const DRIVELINE = 'driveline';

    const BODY_STYLE = 'body_style';

    const ENGINE_TYPE = 'engine_type';

    const MODEL_RANGE = 'model_range';

    const MODEL_DESCRIPTION = 'model_description';

    const TRANSMISSION = 'transmission';

    const STATUS = 'status';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

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
     * Vehicle title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set vehicle title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Vehicle brand
     *
     * @return string|null
     */
    public function getBrand();

    /**
     * Set vehicle brand
     *
     * @param string $brand
     * @return $this
     */
    public function setBrand($brand);

    /**
     * Vehicle model year
     *
     * @return string|null
     */
    public function getModelYear();
    /**
     * Set vehicle model year
     *
     * @param string $modelYear
     * @return $this
     */
    public function setModelYear($modelYear);

    /**
     * Vehicle model code
     *
     * @return string|null
     */
    public function getModelCode();
    /**
     * Set vehicle model code
     *
     * @param string $modelCode
     * @return $this
     */
    public function setModelCode($modelCode);

    /**
     * Vehicle series name
     *
     * @return string|null
     */
    public function getSeriesName();

    /**
     * Set vehicle series name
     *
     * @param string $seriesName
     * @return $this
     */
    public function setSeriesName($seriesName);

    /**
     * Vehicle grade
     *
     * @return string|null
     */
    public function getGrade();

    /**
     * Set vehicle grade
     *
     * @param string $grade
     * @return $this
     */
    public function setGrade($grade);

    /**
     * Vehicle driveline
     *
     * @return string|null
     */
    public function getDriveline();

    /**
     * Set vehicle driveline
     *
     * @param string $driveline
     * @return $this
     */
    public function setDriveline($driveline);

    /**
     * Vehicle body style
     *
     * @return string|null
     */
    public function getBodyStyle();

    /**
     * Set vehicle body style
     *
     * @param string $bodyStyle
     * @return $this
     */
    public function setBodyStyle($bodyStyle);

    /**
     * Vehicle engine type
     *
     * @return string|null
     */
    public function getEngineType();

    /**
     * Set vehicle engine type
     *
     * @param string $engineType
     * @return $this
     */
    public function setEngineType($engineType);

    /**
     * Vehicle model range
     *
     * @return string|null
     */
    public function getModelRange();

    /**
     * Set vehicle model range
     *
     * @param string $modelRange
     * @return $this
     */
    public function setModelRange($modelRange);

    /**
     * Vehicle model description
     *
     * @return string|null
     */
    public function getModelDescription();

    /**
     * Set vehicle model description
     *
     * @param string $modelDescription
     * @return $this
     */
    public function setModelDescription($modelDescription);

    /**
     * Vehicle transmission
     *
     * @return string|null
     */
    public function getTransmission();

    /**
     * Set vehicle transmission
     *
     * @param string $transmission
     * @return $this
     */
    public function setTransmission($transmission);

    /**
     * Vehicle status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set vehicle status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Vehicle created date
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set vehicle created date
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Vehicle updated date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set vehicle updated date
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
