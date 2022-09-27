<?php

/**
 * @package     Infosys/CoreChargeAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CoreChargeAttribute\Model\Total;

use Magento\Quote\Model\QuoteValidator;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

class CoreCharge extends AbstractTotal
{
    /**
     * @var QuoteValidator
     */
    protected $quoteValidator = null;

    /**
     * Constructor method
     *
     * @param QuoteValidator $quoteValidator
     */
    public function __construct(
        QuoteValidator $quoteValidator
    ) {
        $this->quoteValidator = $quoteValidator;
    }

    /**
     * Collect grand total address amount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        
        $balance = $quote->getTotalCoreCharge();
        
        $total->setCoreCharge($balance);
        $total->setBaseCoreCharge($balance);

        $total->setTotalAmount('core_charge', $balance);
        $total->setBaseTotalAmount('core_charge', $balance);

        return $this;
    }

    /**
     * Setting for clearing the grand total values
     *
     * @param Total $total
     */
    protected function clearValues(
        Total $total
    ) {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setTotalAmount('core_charge', 0);
        $total->setBaseTotalAmount('core_charge', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
    
    /**
     * Assign subtotal amount and label to address object
     *
     * @param Quote $quote
     * @param Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(
        Quote $quote,
        Total $total
    ) {
        return [
            'code' => 'core_charge',
            'title' => 'Core Charge',
            'value' => $quote->getTotalCoreCharge()
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Core Charge');
    }
}
