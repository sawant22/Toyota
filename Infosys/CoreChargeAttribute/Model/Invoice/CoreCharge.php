<?php

/**
 * @package     Infosys/CoreChargeAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CoreChargeAttribute\Model\Invoice;

/**
 * Class to update invoice grand total
 */
class CoreCharge extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{

    /**
     * Update the invoice total with Core Charges
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(
        \Magento\Sales\Model\Order\Invoice $invoice
    ) {
        $amount = $invoice->getOrder()->getTotalCoreCharge();
        $invoice->setCoreCharge($amount);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $amount);

        return $this;
    }
}
