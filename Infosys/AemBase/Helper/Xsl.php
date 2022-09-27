<?php

/**
 * @package     Infosys/AemBase
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\AemBase\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class to form the product link for the product
 */
class Xsl extends AbstractHelper
{

    /**
     * Form the product link for the product
     *
     * @param int $storeId
     * @param string $url
     * @return string
     */
    public static function getProductPath($storeId, $url)
    {
        // Needs to use the object manager as this is a static function (which is required for XSL)
        $storeManager = ObjectManager::getInstance()->create('\Magento\Store\Model\StoreManagerInterface');
        $storeData = $storeManager->getStore($storeId);
        $storeCode = (string)$storeData->getCode();

        // AEM Path
        $storeAEMPath = ObjectManager::getInstance()
            ->create('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue(
                'aem_general_config/general/aem_path',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            );

        // Product Path
        $storeProductPath = ObjectManager::getInstance()
            ->create('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue(
                'aem_general_config/general/aem_product_path',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            );

        //concat & form the Product Link
        $productLink = $storeAEMPath . $storeProductPath . $url;

        return $productLink;
    }
}
