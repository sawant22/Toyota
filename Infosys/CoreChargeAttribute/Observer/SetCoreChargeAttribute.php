<?php

/**
 * @package     Infosys/CoreChargeAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CoreChargeAttribute\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Pricing\Helper\Data;

class SetCoreChargeAttribute implements ObserverInterface
{
    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Data
     */
    protected $priceHelper;

    /**
     * Constructor Method
     *
     * @param ProductRepository $productRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param Json $json
     * @param Data $priceHelper
     */
    public function __construct(
        ProductRepository $productRepository,
        CartRepositoryInterface $quoteRepository,
        Json $json,
        Data $priceHelper
    ) {
        $this->_productRepository = $productRepository;
        $this->quoteRepository = $quoteRepository;
        $this->json = $json;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Update the core charges value on both quote and quoteitem
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getDataByKey('quote');
        
        if($quote){
            $quoteItems = $quote->getAllVisibleItems();                    
            $totalCoreCharge = 0;
            $corecharge_detail = [];
            $products = [];
            $quoteDetails = '';

            foreach ($quoteItems as $item) {
                $_product = $this->getProductById($item->getProductId());
                $partNumber = $_product->getPartNumber();
                $core_charge = $_product->getCoreCharge() ?  $_product->getCoreCharge() : 0;
                $item->setCoreCharge($core_charge);
                $item->setPartNumber($partNumber);
            }
            foreach ($quoteItems as $item) {
                if ($item->getCoreCharge() != "null" && $item->getCoreCharge() != 0) {
                    $item_coreCharge =  $item->getCoreCharge() * $item->getQty();
                    $totalCoreCharge = $totalCoreCharge + $item_coreCharge;
                    $corecharge_detail["totalCoreCharge"] = $totalCoreCharge;
                    $products[] = [
                        'SKU' => $item->getSku(),
                        'PartNumber' => $item->getPartNumber(),
                        'Quantity' => intval($item->getQty()),
                        'Core_charge' => $item->getCoreCharge()
                    ];
                }
            }

            $corecharge_detail["Individual"] = $products;
            $core_charge_data = $this->getJsonEncode($corecharge_detail);
            $quote->setCoreChargeDetails($core_charge_data);
            $quote->setTotalCoreCharge($totalCoreCharge);
        }            
    }

    /**
     * Get the product by productId
     *
     * @param String $id
     * @return object
     */
    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    /**
     * Json data
     *
     * @param array $response
     * @return string
     */
    public function getJsonEncode($response)
    {
        return $this->json->serialize($response); // it's same as like json_encode
    }

    /**
     * Json data
     *
     * @param array $response
     * @return array
     */
    public function getJsonDecode($response)
    {
        return $this->json->unserialize($response); // it's same as like json_decode
    }
}
