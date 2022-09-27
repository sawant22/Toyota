<?php

/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\PriceAdjustment\Helper;

use \Infosys\PriceAdjustment\Model\MediaFactory;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Store\Model\ScopeInterface;

/**
 * Helper Data class
 */
class Data extends AbstractHelper
{

    const XML_LOG_ENABLED = 'discount/discount_configuration/enable_logging';
    
    const XML_CRON_ENABLED = 'discount/discount_configuration/enable_cronjob';
    
    const XML_RABBITMQ_ENABLED = 'discount/discount_configuration/tier_price_import_rabbitmq';

    public MediaFactory $mediaFactory;
     
    public ProductFactory $productModel;
   
    public StoreManagerInterface $storeManager;

    /**
     * Construct function
     *
     * @param Context $context
     * @param MediaFactory $mediaFactory
     * @param ProductFactory $productModel
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        MediaFactory $mediaFactory,
        ProductFactory $productModel,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->mediaFactory = $mediaFactory;
        $this->productModel = $productModel;
        $this->storeManager = $storeManager;
    }

    /**
     * GetProductMediaSetPrice function
     *
     * @param int $productId
     * @param float|null $saleprice
     * @return float
     */
    public function getProductMediaSetPrice($productId, $saleprice = null)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $productModel = $this->productModel->create()->load($productId);
        $mediaDataQuery = $this->mediaFactory->create()->getCollection()
            ->addFieldToFilter('media_set_selector', $productModel->getMediaSetCatalog())
            ->addFieldToFilter('website', $websiteId);
        $price = $productModel->getPrice();
        $mediaData = $mediaDataQuery->getData();
        $cost = $productModel->getCost();
        if (!empty($mediaData) && $cost > 0) {
            //if (!$this->isDecimal($mediaData[0]['percentage'])) {
            //return $price;
            //}
            $percentage = $mediaData[0]['percentage'];
            $percentage = (($percentage * 100) - 100);

            if ($mediaData[0]['adjustment_type'] == 1) {
                $newPrice = $cost + (($cost * $percentage) / 100);
                return $newPrice;
            } else {
                return ($price - (($price * $percentage) / 100));
            }
        } else {
            return $price;
        }
        return $price;
    }

    /**
     * Validate decimal value
     *
     * @param float $val
     */
    public function isDecimal($val)
    {
        return is_numeric($val) && floor($val) != $val;
    }

    /**
     * Is logging enabled
     * @return bool
     */
    public function isLoggingEnabled() : bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_LOG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Is Cron for Media Set Selector Options Enabled
     * @return bool
     */
    public function isCronEnabled() : bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_CRON_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Is RabbitMQ Used for tier price calculation or not
     * @return bool
     */
    public function isRabbitMQEnabled() : bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_RABBITMQ_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
