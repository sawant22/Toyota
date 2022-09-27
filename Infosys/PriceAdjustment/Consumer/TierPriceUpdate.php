<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\PriceAdjustment\Consumer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\SpecialPriceInterface;
use Infosys\PriceAdjustment\Model\TierFactory;
use Infosys\PriceAdjustment\Model\MediaFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\Price\SpecialPrice;
use Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory;

/**
 * Class Tier Price Update
 */
class TierPriceUpdate
{
    private ProductRepositoryInterface $productRepository;
    
    private LoggerInterface $loggerManager;
    
    private StoreManagerInterface $storeManager;

    private SpecialPriceInterface $specialPrices;
     
    private TierFactory $tierFactory;

    protected MediaFactory $mediaFactory;
     
    protected ProductFactory $product;
     
    public Json $serializer;
    
    private CollectionFactory $productCollection;

    protected SearchCriteriaBuilder $searchCriteriaBuilder;
         
    private SpecialPrice $specialPrice;

    protected SpecialPriceInterfaceFactory $specialPriceFactory;

    /**
     * PriceInterface import constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $loggerManager
     * @param StoreManagerInterface $storeManager
     * @param SpecialPriceInterface $specialPrices
     * @param SpecialPrice $specialPrice
     * @param TierFactory $tierFactory
     * @param MediaFactory $mediaFactory
     * @param ProductFactory $product
     * @param Json $serializer
     * @param CollectionFactory $productCollection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SpecialPriceInterfaceFactory $specialPriceFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        LoggerInterface $loggerManager,
        StoreManagerInterface $storeManager,
        SpecialPriceInterface $specialPrices,
        SpecialPrice $specialPrice,
        TierFactory $tierFactory,
        MediaFactory $mediaFactory,
        ProductFactory $product,
        Json $serializer,
        CollectionFactory $productCollection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SpecialPriceInterfaceFactory $specialPriceFactory
    ) {
        $this->productRepository = $productRepository;
        $this->loggerManager = $loggerManager;
        $this->storeManager = $storeManager;
        $this->specialPrices = $specialPrices;
        $this->specialPrice = $specialPrice;
        $this->tierFactory = $tierFactory;
        $this->mediaFactory = $mediaFactory;
        $this->product = $product;
        $this->serializer = $serializer;
        $this->productCollection = $productCollection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->specialPriceFactory = $specialPriceFactory;
    }

    /**
     * @param string $tierData
     *
     * @return mixed|void
     */
    public function process($tierData): void
    {
        $tierData = $this->serializer->unserialize($tierData);
        if (isset($tierData['tier_price_set'])) {
            //get products in website and tier price set and attribute set id
            $this->searchCriteriaBuilder->addFilter('tier_price_set', $tierData['tier_price_set'], 'eq');
            $this->searchCriteriaBuilder->addFilter('attribute_set_id', $tierData['tier_price_product_type'], 'eq');
            $this->searchCriteriaBuilder->addFilter('store_id', $tierData['website'], 'eq');
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $productData  = $this->productRepository->getList($searchCriteria)->getItems();
            $this->loggerManager->info("starting batch of " . count($productData) . " products");
            //calculate prices for each product
            $website[] = $tierData['website'];
            $specialPrices = [];
            foreach ($productData as $product) {
                $specialPrices = $this->getSpecialPrice($product->getId(), $website, $product->getSku());
                if(count($specialPrices)){
                    $this->setPricesPerStore($specialPrices);
                }
            }
        } else {
            $product = "";
            $specialPrices = [];
            $website = $tierData['website'];
            $ids = explode(",", $website);
            try {
                $product = $this->productRepository->get($tierData['sku']);
            } catch (\Exception $e) {
                    $this->loggerManager->error('sku not found' . $e->getMessage());
            }
            if ($product->getId()) {
                $sku = $product->getSku();
                $productId = $product->getId();
                $specialPrices = $this->getSpecialPrice($productId, $ids, $sku);
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
     *
     * @return array
     */
    protected function getSpecialPrice($productId, $website, $sku)
    {
        $product = $this->product->create();
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
        $selected_media_set = $ressource->getAttributeRawValue($productId, 'tier_price_set', $websiteId);

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