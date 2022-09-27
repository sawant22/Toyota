<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare (strict_types = 1);

namespace Infosys\PriceAdjustment\Model\ResourceModel;

/**
 * Class for media set resource
 */
class BrandProductType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define init
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('brand_product_type_media_set', 'entity_id');
    }
}
