<?php
/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Block\Adminhtml\Create;

/**
 * Class to create form
 */
class Form extends \Magento\Shipping\Block\Adminhtml\Create\Form
{
    /**
     * Method to get save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/create', ['order_id' => $this->getShipment()->getOrderId()]);
    }
}
