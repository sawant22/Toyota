<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Ui\Component\Form\Element;

class DataProvider extends \Magento\Ui\Component\Form\Element\Input
{
  /**
   * Prepare component configuration
   *
   * @return void
   */
    public function prepare()
    {
        parent::prepare();

        $dynamicValue = 123;

        $config = $this->getData('config');

        if (isset($config['dataScope']) && $config['dataScope']=='your_field_id') {
            $config['default']= $dynamicValue;
            $this->setData('config', (array)$config);
        }
    }
}
