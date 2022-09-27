<?php
/**
 * @package     Infosys/WebsiteProductsMapping
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\WebsiteProductsMapping\Consumer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ResourceConnection;
use Infosys\WebsiteProductsMapping\Logger\ProductsMappingLogger;

/**
 * Consumer class for website products mapping
 */
class AssignPrductsToWebsite
{
    const CATALOG_PRODUCT_WEBSITE = "catalog_product_website";

    const CATALOG_PRODUCT_ENTITY = "catalog_product_entity";

    protected ResourceConnection $resource;

    private ProductsMappingLogger $loggerManager;
    
    /**
     * Initialize dependencies
     *
     * @param ResourceConnection $resource
     * @param ProductsMappingLogger $loggerManager
     */
    public function __construct(
        ResourceConnection $resource,
        ProductsMappingLogger $loggerManager
    ) {
        $this->_connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->logger = $loggerManager;
    }

    /**
     * Method to link products with website
     *
     * @param string $websiteId
     * @return mixed|void
     */
    public function process($websiteId): void
    {
        $this->logger->info("website products mapping consumer");
        try {
            $select = $this->_connection->select()->from(
                self::CATALOG_PRODUCT_ENTITY
            );
            $productIds = $this->_connection->fetchCol($select);
            
            $finalData = [];
            if (!empty($productIds)) {
                array_walk(
                    $productIds,
                    function (&$val, $key) use (&$finalData, $websiteId) {
                        $finalData[] = ['product_id'=>$val, 'website_id'=>$websiteId];
                    }
                );

                //assign products to website
                $this->_connection->insertOnDuplicate(
                    self::CATALOG_PRODUCT_WEBSITE,
                    $finalData,
                    ['product_id']
                );
            }
        } catch (\Exception $e) {
            $this->logger->error("Error in assigning products to website from admin ".$e->getMessage());
        }
    }
}
