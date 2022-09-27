<?php
/**
 * @package     Infosys/ShippingRestriction
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\ShippingRestriction\Plugin\Model;

use Infosys\ShippingRestriction\Helper\Data;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Exception\InputException;
use \Magento\Quote\Model\QuoteRepository;

class ShippingAddressManagement
{
    /**
     *
     * @var quoteRepository
     */
    protected $quoteRepository;
    /**
     *
     * @var scopeConfig
     */
    protected $scopeConfig;
    /**
     *
     * @var helperData
     */
    protected $helperData;

    /**
     * Construct function
     *
     * @param QuoteRepository $quoteRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $helperData
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        ScopeConfigInterface $scopeConfig,
        Data $helperData
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
    }

    /**
     * BeforeAssign function
     *
     * @param \Magento\Quote\Model\ShippingAddressManagement $subject
     * @param string $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return void
     */
    public function beforeAssign(
        \Magento\Quote\Model\ShippingAddressManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {
        $this->helperData->validateStateEnabled($address->getRegion());
        $this->helperData->checkPOBoxAddress($address);
    }
}
