<?php
/**
 * @package     Infosys/ShippingRestriction
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\ShippingRestriction\Plugin\Model;

use Infosys\ShippingRestriction\Helper\Data;
use \Magento\Quote\Model\PaymentMethodManagement as CorePaymentMethodManagement;
use \Magento\Quote\Model\QuoteRepository;

class PaymentMethodManagement
{
    /**
     *
     * @var quoteRepository
     */
    public $quoteRepository;

    /**
     *
     * @var helperData
     */
    public $helperData;

    /**
     * Construct function
     *
     * @param QuoteRepository $quoteRepository
     * @param Data $helperData
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        $this->quoteRepository = $quoteRepository;
    }
    /**
     * BeforeSet function
     *
     * @param CorePaymentMethodManagement $subject
     * @param string $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $method
     * @return void
     */
    public function beforeSet(
        CorePaymentMethodManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $method
    ) {
        $quote = $this->quoteRepository->get($cartId);
        $address = $quote->getShippingAddress();
        $this->helperData->validateStateEnabled($address->getRegion());
        $this->helperData->checkPOBoxAddress($address);
    }
}
