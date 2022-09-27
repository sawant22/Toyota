<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright � 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Block\Adminhtml\Create;

class Items extends \Magento\Shipping\Block\Adminhtml\Create\Items
{
    /**
     * Prepare child blocks
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->addChild(
            'submit_df_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Submit Direct Fulfillment'),
                'class' => 'save submit-button primary',
                'onclick' => 'submitShipment(this);'
            ]
        );

        return parent::_beforeToHtml();
    }
}
