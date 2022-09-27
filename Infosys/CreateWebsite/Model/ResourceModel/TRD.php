<?php

/**
 * @package Infosys/CreateWebsite
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CreateWebsite\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Infosys\CreateWebsite\Api\Data\TRDInterface;

class TRD extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(TRDInterface::TRD_TABLE, TRDInterface::ID);
    }
}
