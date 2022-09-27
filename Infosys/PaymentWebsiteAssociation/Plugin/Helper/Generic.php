<?php

/**
 * @package     Infosys/PaymentWebsiteAssociation
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\PaymentWebsiteAssociation\Plugin\Helper;

class Generic
{
    /**
     * Overriding the metod to include 'selected_plan' for graphql query
     *
     * @param \StripeIntegration\Payments\Helper\Generic $subject
     * @param  $payment
     * @param  $data
     * @param  $useStoreCurrency
     * @return array
     */
    public function beforeAssignPaymentData(
        \StripeIntegration\Payments\Helper\Generic $subject,
        $payment,
        $data,
        $useStoreCurrency
    ) {
        if (!isset($data['selected_plan'])) {
            $data['selected_plan'] = null;
        }
        return [$payment, $data, $useStoreCurrency];
    }
}
