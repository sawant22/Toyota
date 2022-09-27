<?php

/**
 * @package     Infosys/SalesReport
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Resource model class for toyota dealer sales statistics queue table
 */
class DealerSalesStatisticsQueue extends AbstractDb
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
        $this->_init('toyota_dealer_sales_statistics_queue', 'entity_id');
    }
}
