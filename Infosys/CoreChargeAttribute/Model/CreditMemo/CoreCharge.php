<?php

/**
 * @package     Infosys/CoreChargeAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CoreChargeAttribute\Model\CreditMemo;

/**
 * Class to update creditmemo grand total
 */
class CoreCharge extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{

    /**
     * Update the creditmemo total with Core Charges
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(
        \Magento\Sales\Model\Order\Creditmemo $creditmemo
    ) {

        $amount = $creditmemo->getOrder()->getTotalCoreCharge();
        $creditmemo->setCoreCharge($amount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $amount);

        return $this;
    }
}
