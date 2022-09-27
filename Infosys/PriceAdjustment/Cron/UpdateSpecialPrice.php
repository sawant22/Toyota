<?php

/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\PriceAdjustment\Cron;

use Magento\Store\Model\StoreManagerInterface;
use Infosys\PriceAdjustment\Model\MediaFactory;
use Infosys\PriceAdjustment\Model\TierFactory;
use Infosys\PriceAdjustment\Model\TierQueueFactory;
use Magento\Catalog\Model\ResourceModel\Product\Price\SpecialPrice;
use Magento\Catalog\Api\Data\SpecialPriceInterface;
use Infosys\PriceAdjustment\Logger\PriceCalculationLogger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Infosys\PriceAdjustment\Model\Config\Configuration;
use Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory;

class UpdateSpecialPrice
{
    /**
     * @var TierQueueFactory
     */
    private $tierQueueFactory;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SpecialPriceInterface
     */
    private $specialPrice;

    /**
     * @var specialPrices
     */
    private $specialPrices;

    /**
     * @var MediaFactory
     */
    protected $mediaFactory;
    /**
     * @var tierModel
     */

    protected $tierModel;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
     /**
      * @var ScopeConfigInterface
      */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    protected FilterBuilder $filterBuilder;

    /**
     * @var FilterGroup
     */
    protected $filterGroupBuilder;

    protected Configuration $dealerPriceConfig;

    protected SpecialPriceInterfaceFactory $specialPriceFactory;

     /**
     * @param ResourceConnection $resourceConnection
     * @param PriceCalculationLogger $logger
     * @param TierQueueFactory $tierQueueFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param SpecialPrice $specialPrice
     * @param SpecialPriceInterface $specialPrices
     * @param MediaFactory $mediaFactory
     * @param TierFactory $tierModel
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroup $filterGroupBuilder
     * @param Configuration $dealerPriceConfig
     * @param SpecialPriceInterfaceFactory $specialPriceFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        PriceCalculationLogger $logger,
        TierQueueFactory $tierQueueFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $product,
        SpecialPrice $specialPrice,
        SpecialPriceInterface $specialPrices,
        MediaFactory $mediaFactory,
        TierFactory $tierModel,
        ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroup $filterGroupBuilder,
        Configuration $dealerPriceConfig,
        SpecialPriceInterfaceFactory $specialPriceFactory
    ) {
        $this->resource = $resourceConnection;
        $this->logger = $logger;
        $this->tierQueueFactory = $tierQueueFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->product = $product;
        $this->specialPrice = $specialPrice;
        $this->specialPrices = $specialPrices;
        $this->mediaFactory = $mediaFactory;
        $this->tierFactory = $tierModel;
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->dealerPriceConfig = $dealerPriceConfig;
        $this->specialPriceFactory = $specialPriceFactory;
        $this->connection  = $resourceConnection->getConnection();
    }

    /**
     * Execute action
     */
    public function execute()
    {
        //Disable dealer price calculation using cron job 
        if(!$this->dealerPriceConfig->enableDealerPriceCalcDuringImport()) {
            $this->logger->info("Dealer price calculation using cron job is disabled");
            return false;
        }

        $this->logger->info("Calculating Prices");

        //fetch tier queue data that contains website and tier_price_set (sku is blank)
        $tierQueue = $this->tierQueueFactory->create();
        $tierCollection = $tierQueue->getCollection()
            ->addFieldToFilter('special_price_update_status', 'N')
            ->addFieldToFilter('sku', '')
            ->getFirstItem()->getData();

        if (!empty($tierCollection)) {
            $this->logger->info("Calculating prices for Tier Set");
            $this->calculatePricesForTierSet($tierCollection);
        } else {
            $this->logger->info("Calculating prices for list of products");
            //Tier queue data contains website and sku (tier_price_set is blank)
            $tierQueue = $this->tierQueueFactory->create();
            $tierCollection = $tierQueue->getCollection()
                ->addFieldToFilter('special_price_update_status', 'N')
                ->addFieldToFilter('tier_price_set', '');
            $tierCollection->getSelect()->limit($this->getLimit());
            $this->calculatePricesForProducts($tierCollection);
        }
        $this->logger->info("Price Calculations Complete");
    }

   /**
     * Get collection limit to process
     *
     * @return void
     */
    private function getLimit()
    {
        return $this->scopeConfig->getValue(
            'discount/discount_configuration/maximum_product_count',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Calculate prices for tier queue entry that contains tier_price_set and single website
     *
     * @param [type] $tierCollection
     * @return void
     */
    protected function calculatePricesForTierSet($tierCollection)
    {
        //Set to Y up front so it doesn't process again when cron runs
        $tierQueue = $this->tierQueueFactory->create();
        $tiers = $tierQueue->load($tierCollection['entity_id']);
        $tiers->setData('special_price_update_status', 'Y');
        $tiers->save();

        $websiteId = $tierCollection['website'];
        $tierPriceSet = $tierCollection['tier_price_set'];
        $productType = $tierCollection['tier_price_product_type'];
        $this->logger->info("website: " . $websiteId);
        $this->logger->info("tier_price_set: " . $tierPriceSet);
        $this->logger->info("tier_price_product_type: " . $productType);
        $website[] = $websiteId;
        $specialPrices = [];

        //calculate in batches to avoid out of memory exceptions
        $page = 1;
        $numProcessed = 0;        
        do {
            //get products in website and tier price set and attribute set id
            $this->searchCriteriaBuilder->addFilter('tier_price_set', $tierPriceSet, 'eq');
            $this->searchCriteriaBuilder->addFilter('attribute_set_id', $productType, 'eq');
            $this->searchCriteriaBuilder->setPageSize($this->getLimit());
            $this->searchCriteriaBuilder->setCurrentPage($page);
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $productData  = $this->productRepository->getList($searchCriteria)->getItems();
            $totalProducts = $this->productRepository->getList($searchCriteria)->getTotalCount();

            $this->logger->info("starting batch of " . count($productData) . " products");
            //calculate prices for each product
            foreach ($productData as $product) {
                $specialPrices = $this->getSpecialPrice($product->getId(), $website, $product->getSku());
                if(count($specialPrices)){
                    $this->setPricesPerStore($specialPrices);
                }
            }           

            $numProcessed += count($productData);
            $page++;
            $this->logger->info("finished processing " . $numProcessed . " of " . $totalProducts . " products");
        } while ($numProcessed < $totalProducts);
    }

    /**
     * Calculate Prices for list of tier queue records that contain sku's
     *
     * @param [type] $tierQueue
     * @return void
     */
    protected function calculatePricesForProducts($tierCollection)
    {
        $updatedSkus = [];
        $specialPrices = [];
        $updateTierQueueData = [];
        if (!empty($tierCollection)) {
            foreach ($tierCollection as $tierData) {
                $tierQueue = $this->tierQueueFactory->create();
                $this->logger->info("Updating price for SKU: " . $tierData['sku']);
                $product = "";
                $website = $tierData['website'];
                $ids = explode(",", $website);
                $updatedSkus[] = $tierData['sku'];
                try {
                    $product = $this->productRepository->get($tierData['sku']);
                } catch (\Exception $e) {
                    $tierQueue->load($tierData['entity_id'])->delete();
                }
                if ($product) {
                    $sku = $product->getSku();
                    $productId = $product->getId();
                    $specialPrices = $this->getSpecialPrice($productId, $ids, $sku);
                    if(count($specialPrices) > 0 ){
                        $this->setPricesPerStore($specialPrices);
                    }
                }                

                $updateTierQueueData[] = [
                    "entity_id"=> $tierData['entity_id'],
                    "special_price_update_status"=> 'Y'
                ];

            }

            try {                
                //Update the status on tier_queue table
                if(count($updateTierQueueData) > 0 ){
                    $tableName = $this->resource->getTableName("tier_queue");
                    $this->connection->insertOnDuplicate($tableName, $updateTierQueueData);
                }
                
            } catch (\Exception $e) {
                $this->logger->error('Tier queue table update error' . $e->getMessage());
            }

            $this->logger->info(" Special Price Updated Skus-" . implode(',', $updatedSkus));
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