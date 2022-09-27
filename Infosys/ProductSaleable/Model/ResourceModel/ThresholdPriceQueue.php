<?php

/**
 * @package     Infosys/ProductSaleable
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\ProductSaleable\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class to initialize resource model of thresholdpricequeue
 */
class ThresholdPriceQueue extends AbstractDb
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
        $this->_init('threshold_price_queue', 'entity_id');
    }
}
