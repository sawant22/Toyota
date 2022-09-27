<?php

/**
 * @package Infosys/CreateWebsite
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CreateWebsite\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Infosys\CreateWebsite\Api\Data\TRDInterface;


class TRD extends AbstractExtensibleModel implements TRDInterface
{
    /**
     * Initialize Resource Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\TRD::class);
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
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getRegionCode()
    {
        return parent::getData('region_code');
    }

    /**
     * @inheritDoc
     */
    public function setRegionCode($code)
    {
        return $this->setData('region_code', $code);
    }

    /**
     * @inheritDoc
     */
    public function getRegionLabel()
    {
        return parent::getData('region_label');
    }

    /**
     * @inheritDoc
     */
    public function setRegionLabel($label)
    {
        return $this->setData('region_label', $label);
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
