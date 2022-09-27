<?php

/**
 * @package     Infosys/AdminRole
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\AdminRole\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

/**
 * Class to create new website
 */
class Data extends AbstractHelper
{
    /**
     *
     * @var CollectionFactory
     */
    private $websiteCollection;
    /**
     * Constructor function
     *
     * @param Context $context
     * @param CollectionFactory $websiteCollection
     */
    public function __construct(
        Context $context,
        CollectionFactory $websiteCollection
    ) {
        parent::__construct($context);
        $this->websiteCollection = $websiteCollection;
    }

    /**
     * Get Dealer Website Id
     *
     * @param string $dealerCode
     * @return void
     */
    public function getDealerWebsite($dealerCode)
    {
        $websiteCollection = $this->websiteCollection->create();
        $websiteCollection->addFieldToFilter('dealer_code', ['eq' => $dealerCode]);
        return $websiteCollection->getAllIds();
    }

    /**
     * Get all Regional website ids
     *
     * @param string $regionCode
     * @return void
     */
    public function getRegionalWebsite($regionCode)
    {
        $websiteCollection = $this->websiteCollection->create();
        $websiteCollection->join('toyota_dealer_regions', 'region_id')->
        addFilterToMap('region_code', 'toyota_dealer_regions.region_code')->
        addFieldToFilter('region_code', ['eq' => $regionCode]);
        return $websiteCollection->getAllIds();
    }
}
