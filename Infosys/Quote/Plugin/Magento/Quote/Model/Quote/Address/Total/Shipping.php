<?php

/**
 * @package Infosys/Quote
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Quote\Plugin\Magento\Quote\Model\Quote\Address\Total;

use Magento\Quote\Model\Quote\Address\Total\Shipping as QuoteShipping;

/**
 * Class to update shipping description for Quote
 */
class Shipping
{

    /**
     * After Plugin to update shipping description
     *
     * @param QuoteShipping $subject
     * @param $result
     * @param $quote
     * @param $shippingAssignment
     * @param $total
     * @return void
     */
    public function afterCollect(
        QuoteShipping $subject,
        $result,
        $quote,
        $shippingAssignment,
        $total
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();
        $method = $shippingAssignment->getShipping()->getMethod();

        if (!count($shippingAssignment->getItems())) {
            return $result;
        }

        if ($method) {
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode() == $method) {
                    $shippingDescription = $rate->getCarrierTitle();
                    $address->setShippingDescription($shippingDescription);
                    $total->setShippingDescription($address->getShippingDescription());
                    break;
                }
            }
        }
        return $result;
    }
}
