<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model\ResourceModel;

class Tier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define init
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tier_price', 'id');
    }
}
