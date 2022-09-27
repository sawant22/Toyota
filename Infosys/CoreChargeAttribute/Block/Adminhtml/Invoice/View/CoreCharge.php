<?php

/**
 * @package     Infosys/CoreChargeAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CoreChargeAttribute\Block\Adminhtml\Invoice\View;

/**
 * Class to override the sales Invoice view page content
 */
class CoreCharge extends \Magento\Framework\View\Element\Template
{

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Get the store
     *
     * @return String
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Get the order details
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get the label properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get the value properties
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
    
    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        $store = $this->getStore();
        $totalCoreCharge = $this->_order->getTotalCoreCharge();
        
        $fee = new \Magento\Framework\DataObject(
            [
               'code' => 'core_charge',
               'strong' => false,
               'value' => $totalCoreCharge,
               'label' => __('Core Charges'),
            ]
        );

        if ($totalCoreCharge != "null" && $totalCoreCharge != 0) {
            $parent->addTotal($fee, 'core_charge');
        }

        return $this;
    }
}
