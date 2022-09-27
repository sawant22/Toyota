<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Model\ResourceModel\FreightRecovery;

use Infosys\DirectFulFillment\Model\ResourceModel\FreightRecovery as ResourceModelFreightRecovery;
use Infosys\DirectFulFillment\Model\FreightRecovery as ModelFreightRecovery;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ModelFreightRecovery::class, ResourceModelFreightRecovery::class);
    }
}
