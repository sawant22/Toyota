<?php

/**
 * @package Infosys/CreateWebsite
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CreateWebsite\Model\ResourceModel\TRD;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Infosys\CreateWebsite\Model\ResourceModel\TRD as ResourceModelTRD;
use Infosys\CreateWebsite\Model\TRD as ModelTRD;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ModelTRD::class,
            ResourceModelTRD::class
        );
    }
}
