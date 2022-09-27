<?php

/**
 * @package     Infosys/ProductSaleable
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\ProductSaleable\Model\ResourceModel\ThresholdPriceQueue;

use Infosys\ProductSaleable\Model\ResourceModel\ThresholdPriceQueue as ResourceModelThresholdPriceQueue;
use Infosys\ProductSaleable\Model\ThresholdPriceQueue as ModelThresholdPriceQueue;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class to initialize collection of thresholdpricequeue
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
        $this->_init(ModelThresholdPriceQueue::class, ResourceModelThresholdPriceQueue::class);
    }
}
