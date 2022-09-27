<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Infosys\PriceAdjustment\Model\TierQueueFactory;
use Infosys\PriceAdjustment\Model\TierFactory;
use Infosys\PriceAdjustment\Model\MediaFactory;
use Magento\Catalog\Model\ResourceModel\Product\Price\SpecialPrice;
use Magento\Catalog\Api\Data\SpecialPriceInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory;

class UpdateProductQueue implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var TierQueueFactory
     */
    private $tierQueueFactory;

    /**
     * @var MediaFactory
     */
    protected $mediaFactory;

    /**
     * @var TierFactory
     */
    protected $tierFactory;

    /**
     * @var SpecialPriceInterface
     */
    private $specialPrice;

    /**
     * @var specialPrices
     */
    private $specialPrices;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected SpecialPriceInterfaceFactory $specialPriceFactory;
    
    /**
     * @param StoreManagerInterface $storeManager
     * @param TierQueueFactory $tierQueueFactory
     * @param MediaFactory $mediaFactory
     * @param TierFactory $tierFactory
     * @param SpecialPrice $specialPrice
     * @param SpecialPriceInterface $specialPrices
     * @param LoggerInterface $logger
     * @param SpecialPriceInterfaceFactory $specialPriceFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        TierQueueFactory $tierQueueFactory,
        MediaFactory $mediaFactory,
        TierFactory $tierFactory,
        SpecialPrice $specialPrice,
        SpecialPriceInterface $specialPrices,
        LoggerInterface $logger,
        SpecialPriceInterfaceFactory $specialPriceFactory
    ) {
        $this->storeManager = $storeManager;
        $this->tierQueueFactory = $tierQueueFactory;
        $this->mediaFactory = $mediaFactory;
        $this->tierFactory = $tierFactory;
        $this->specialPrice = $specialPrice;
        $this->specialPrices = $specialPrices;
        $this->logger = $logger;
        $this->specialPriceFactory = $specialPriceFactory;
    }
    
    /**
     * Execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $sku = $product->getSku();
        $tierSet = $product->getTierPriceSet();
        $websiteIds = $product->getWebsiteIds();
 
        if (!empty($tierSet)) {
            $tierQueue = $this->tierQueueFactory->create();
            if ($product) {
                $sku = $product->getSku();
                $productId= $product->getId();
                $specialPrices = $this->getSpecialPrice($productId, $websiteIds, $sku, $product);
                if(count($specialPrices) > 0 ){
                    $this->setPricesPerStore($specialPrices);
                }
            }
        }
    }

    /**
     * Set special price for products.
     *
     * @param array $specialPrices
     * @return bool
     * @throws \Exception
     */
    protected function setPricesPerStore($specialPrices)
    {
        try {
            foreach($specialPrices as $price){
                $this->logger->info("set price to " . $price['price'] . " on " . $price['sku'] . " for website ID " . $price['store_id']);
                $specialPrice = $this->specialPriceFactory->create();
                $updateSpecialPrice[] = $specialPrice->setSku($price['sku'])
                    ->setStoreId($price['store_id'])
                    ->setPrice($price['price']);
            }

            $this->specialPrice->update($updateSpecialPrice);
        } catch (\Exception $e) {
            $this->logger->error('special price update error' . $e->getMessage());
        }
        return true;
    }

    /**
     * Return special price.
     *
     * @param int $productId
     * @param array $website
     * @param string $sku
     * @param object $product
     *
     * @return array
     */
    protected function getSpecialPrice($productId, $website, $sku, $product)
    {
        $productById = $product->loadByAttribute('entity_id', $productId);
        $ressource = $product->getResource();        
        //Getting the default website price and cost
        if(count($website) == 1){
            $websiteId = $website[0];
        }else{
            $websiteId = "1";
        }
        $originalprice =  $ressource->getAttributeRawValue($productId, 'price', $websiteId);
        $cost = $ressource->getAttributeRawValue($productId, 'cost', $websiteId);
        $selected_media_set = $productById->getTierPriceSet();

        if (!empty($selected_media_set)) {

            $mediaData = $this->getMediaData($originalprice, $website, $selected_media_set);

            $pricePerWebsite = [];
            foreach ($website as $websiteId) {
                $storeId = $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
                if (!empty($mediaData) && array_key_exists($websiteId, $mediaData)) {
                    $tierData = $mediaData;
                    $percentage = $tierData[$websiteId]['percentage'];
                    if (!empty($cost)) {
                        $price = $cost;
                        if ($tierData[$websiteId]['adjustment_type'] == 1) {
                            $newPrice = $price + (($price*$percentage)/100);                        
                            if($newPrice > $originalprice) {
                                $originalProductPrice =  $originalprice;
                            }
                            else{
                                $originalProductPrice =  $newPrice;
                            }
                        } else {
                            $originalProductPrice = ($originalprice - (($originalprice * $percentage) / 100));
                        }
                    } else {
                        $originalProductPrice =  $originalprice;
                    }
                }
                else{
                    $originalProductPrice =  $originalprice;
                }
                
                $pricePerWebsite[] = [
                    "price" => $originalProductPrice,
                    "store_id" => $storeId,
                    "sku"=> $sku
                ];
            }
        }
        return $pricePerWebsite;
    }

    /**
     * Get media data by selected media set & original price & cost
     * 
     * @param float $originalprice
     * @param array $website
     * @param string $selected_media_set
     * @return array
     */
    public function getMediaData($originalprice, $websites, $selected_media_set) : array
    {
        $tierPriceMediaData = [];
        $collection = $this->mediaFactory->create()->getCollection();
        $collection->getSelect()->join(array('tp' => 'tier_price'),
            'main_table.entity_id = tp.entity_id')
            ->where('main_table.media_set_selector=?',$selected_media_set)
            ->where('main_table.website IN (?)',$websites)
            ->where('tp.from_price <=?',(float)$originalprice)
            ->where('tp.to_price >=?',(float)$originalprice);
        $mediaData = $collection->getData();

        foreach($mediaData as $data){
            foreach($websites as $website){
                if($website == $data['website']){
                    $tierPriceMediaData[$data['website']]['percentage'] = $data['percentage'];
                    $tierPriceMediaData[$data['website']]['adjustment_type'] = $data['adjustment_type'];
                }
            }
        }	

        return $tierPriceMediaData;
    }
}