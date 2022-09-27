<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Model\ResourceModel\DealerSalesStatistics;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Infosys\SalesReport\Model\ResourceModel\DealerSalesStatistics as ResourceModelDealerSalesStatistics;
use Infosys\SalesReport\Model\DealerSalesStatistics as ModelDealerSalesStatistics;

/**
 * Collection class for toyota dealer sales statistics table
 */
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
            ModelDealerSalesStatistics::class,
            ResourceModelDealerSalesStatistics::class
        );
    }
}
