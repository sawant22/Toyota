<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class FreightRecovery extends AbstractDb
{
    /**
     * Constructor function
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }
    /**
     * Initialize table data
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('df_sales_order_freight_recovery', 'entity_id');
    }
}
