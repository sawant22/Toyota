<?php
/**
 * @package     Infosys/WebsiteProductsMapping
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\WebsiteProductsMapping\Publisher;

use Magento\Framework\MessageQueue\PublisherInterface;
use Infosys\WebsiteProductsMapping\Logger\ProductsMappingLogger;

/**
 * Publisher Class for website products mapping
 */
class StoreWebsite
{
    public const TOPIC_NAME = "magento.website-product.mapping";

    private PublisherInterface $publisher;

    private ProductsMappingLogger $loggerManager;

    /**
     * Initialize dependencies
     *
     * @param PublisherInterface $publisher
     * @param ProductsMappingLogger $loggerManager
     */
    public function __construct(
        PublisherInterface $publisher,
        ProductsMappingLogger $loggerManager
    ) {
        $this->publisher = $publisher;
        $this->logger = $loggerManager;
    }

    /**
     * Publish method
     *
     * @param integer $websiteId
     * @return bool
     */
    public function publish($websiteId): bool
    {
        $this->logger->info("website products mapping publisher");
        $this->publisher->publish(self::TOPIC_NAME, $websiteId);
        return true;
    }
}
