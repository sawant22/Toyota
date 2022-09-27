<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Ui\Component\CartLog\Localisation;

class Type implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get all options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $TypesArray = [];
        $TypesArray[] = ['value' => 1, 'label' => __('Cost+Percentage')];
        $TypesArray[] = ['value' => 2, 'label' => __('List-Percentage')];

        return $TypesArray;
    }
}
