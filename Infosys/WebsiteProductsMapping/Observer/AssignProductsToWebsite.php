<?php
/**
 * @package Infosys/WebsiteProductsMapping
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright � 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\WebsiteProductsMapping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Infosys\WebsiteProductsMapping\Publisher\StoreWebsite as Publisher;
use Psr\Log\LoggerInterface;

/**
 * Class to link all products to website
 */
class AssignProductsToWebsite implements ObserverInterface
{
    protected LoggerInterface $logger;

    private Publisher $publisher;

    /**
     * Initialize dependencies
     *
     * @param LoggerInterface $logger
     * @param Publisher $publisher
     */
    public function __construct(
        LoggerInterface $logger,
        Publisher $publisher
    ) {
        $this->logger = $logger;
        $this->publisher = $publisher;
    }

    /**
     * Method to link products with website at the time of store creation
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        $store = $observer->getEvent()->getData('store');
        $websiteId = $store->getWebsiteId();
        $this->publisher->publish($websiteId);
    }
}
