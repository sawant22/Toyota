<?php
/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\Vehicle\Ui\Component\Listing\Columns;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value'=>'1', 'label'=>__('Enable')],
            ['value'=>'0', 'label'=>__('Disable')]
        ];
    }
}
