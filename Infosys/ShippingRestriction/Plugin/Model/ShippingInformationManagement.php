<?php
/**
 * @package     Infosys/ShippingRestriction
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\ShippingRestriction\Plugin\Model;

use Infosys\ShippingRestriction\Helper\Data;
use \Magento\Checkout\Api\Data\ShippingInformationInterface;
use \Magento\Checkout\Model\ShippingInformationManagement as CoreShippingInformationManagement;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Exception\InputException;
use \Magento\Quote\Model\QuoteRepository;

class ShippingInformationManagement
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
     * BeforeSaveAddressInformation function for validate state and po box
     *
     * @param CoreShippingInformationManagement $subject
     * @param string|int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return void
     */
    public function beforeSaveAddressInformation(
        CoreShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $address = $addressInformation->getShippingAddress();
        $this->helperData->validateStateEnabled($address->getRegion());
        $this->helperData->checkPOBoxAddress($address);
    }
}
