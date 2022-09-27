<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Infosys\Vehicle\Api\Data\VehicleInterface;

class Vehicle extends AbstractExtensibleModel implements VehicleInterface
{
    /**
     * Initialize Resource Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Vehicle::class);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getBrand()
    {
        return parent::getData(self::BRAND);
    }

    /**
     * @inheritDoc
     */
    public function setBrand($brand)
    {
        return $this->setData(self::BRAND, $brand);
    }

    /**
     * @inheritDoc
     */
    public function getModelYear()
    {
        return parent::getData(self::MODEL_YEAR);
    }

    /**
     * @inheritDoc
     */
    public function setModelYear($modelYear)
    {
        return $this->setData(self::MODEL_YEAR, $modelYear);
    }

    /**
     * @inheritDoc
     */
    public function getModelCode()
    {
        return parent::getData(self::MODEL_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setModelCode($modelCode)
    {
        return $this->setData(self::MODEL_CODE, $modelCode);
    }

    /**
     * @inheritDoc
     */
    public function getSeriesName()
    {
        return parent::getData(self::SERIES_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setSeriesName($seriesName)
    {
        return $this->setData(self::SERIES_NAME, $seriesName);
    }

    /**
     * @inheritDoc
     */
    public function getGrade()
    {
        return parent::getData(self::GRADE);
    }

    /**
     * @inheritDoc
     */
    public function setGrade($grade)
    {
        return $this->setData(self::GRADE, $grade);
    }

    /**
     * @inheritDoc
     */
    public function getDriveline()
    {
        return parent::getData(self::DRIVELINE);
    }

    /**
     * @inheritDoc
     */
    public function setDriveline($driveline)
    {
        return $this->setData(self::DRIVELINE, $driveline);
    }

    /**
     * @inheritDoc
     */
    public function getBodyStyle()
    {
        return parent::getData(self::BODY_STYLE);
    }

    /**
     * @inheritDoc
     */
    public function setBodyStyle($bodyStyle)
    {
        return $this->setData(self::BODY_STYLE, $bodyStyle);
    }

    /**
     * @inheritDoc
     */
    public function getEngineType()
    {
        return parent::getData(self::ENGINE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setEngineType($engineType)
    {
        return $this->setData(self::ENGINE_TYPE, $engineType);
    }

    /**
     * @inheritDoc
     */
    public function getModelRange()
    {
        return parent::getData(self::MODEL_RANGE);
    }

    /**
     * @inheritDoc
     */
    public function setModelRange($modelRange)
    {
        return $this->setData(self::MODEL_RANGE, $modelRange);
    }

    /**
     * @inheritDoc
     */
    public function getModelDescription()
    {
        return parent::getData(self::MODEL_DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setModelDescription($modelDescription)
    {
        return $this->setData(self::MODEL_DESCRIPTION, $modelDescription);
    }

    /**
     * @inheritDoc
     */
    public function getTransmission()
    {
        return parent::getData(self::TRANSMISSION);
    }

    /**
     * @inheritDoc
     */
    public function setTransmission($transmission)
    {
        return $this->setData(self::TRANSMISSION, $transmission);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return parent::getData(self::TRANSMISSION);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::TRANSMISSION, $status);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set updated at time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set created in area
     *
     * @param string $createdIn
     * @return $this
     */
    public function setCreatedAt($createdIn)
    {
        return $this->setData(self::CREATED_AT, $createdIn);
    }
}
