<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface;

class VehicleReplaceData extends AbstractExtensibleModel implements VehicleReplaceDataInterface
{
    /**
     * Initialize Resource Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\VehicleReplaceData::class);
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
    public function getAttribute()
    {
        return parent::getData(self::ATTRIBUTE);
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($attribute)
    {
        return $this->setData(self::ATTRIBUTE, $attribute);
    }

    /**
     * @inheritDoc
     */
    public function getFind()
    {
        return parent::getData(self::FIND);
    }

    /**
     * @inheritDoc
     */
    public function setFind($find)
    {
        return $this->setData(self::FIND, $find);
    }

    /**
     * @inheritDoc
     */
    public function getReplace()
    {
        return parent::getData(self::REPLACE);
    }

    /**
     * @inheritDoc
     */
    public function setReplace($replace)
    {
        return $this->setData(self::REPLACE, $replace);
    }
}
