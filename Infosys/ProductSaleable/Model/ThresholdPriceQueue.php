<?php

/**
 * @package     Infosys/ProductSaleable
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\ProductSaleable\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class to initialize model of thresholdpricequeue
 */
class ThresholdPriceQueue extends AbstractModel
{
    /**
     * Initialize Resource Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\ThresholdPriceQueue::class);
    }
}
