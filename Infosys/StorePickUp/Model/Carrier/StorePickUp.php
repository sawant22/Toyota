<?php

/**
 * @package     Infosys/StorePickUp
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\StorePickUp\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

class StorePickUp extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'dealerstore';
    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;
    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;
    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
    /**
     * Allowed Shipping Methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['dealerstore' => $this->getConfigData('name')];
    }
    /**
     * Collect Rate for Shipping method
     *
     * @param RateRequest $request
     * @return object
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier('dealerstore');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('pickup');
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = $this->getConfigData('price');
        $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);
        $method->setPrice($shippingPrice);
        $method->setCost($amount);

        $result->append($method);

        return $result;
    }
}
