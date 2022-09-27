<?php
/**
 * @package     Infosys/StorePickUp 
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\StorePickUp\Model\Config\Source;

class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
    * Get all options
    *
    * @return array
    */
    public function getAllOptions()
    {
        $this->_options = [
                ['label' => __('No'), 'value'=>'no'],
                ['label' => __('Yes'), 'value'=>'yes']
            ];

		return $this->_options;
    }
}