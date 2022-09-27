<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare (strict_types = 1);

namespace Infosys\PriceAdjustment\Model\ResourceModel\BrandProductType;

/**
 * Class for media set collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected string $idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            \Infosys\PriceAdjustment\Model\BrandProductType::class,
            \Infosys\PriceAdjustment\Model\ResourceModel\BrandProductType::class
        );
    }
}
