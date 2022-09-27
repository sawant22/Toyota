<?php

/**
 * @package     Infosys/CreateWebsite
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CreateWebsite\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\WebsiteFactory;

use Infosys\CreateWebsite\Helper\Data;

/**
 * Class to create websites
 */
class CreateWebsite implements DataPatchInterface
{
    /**
     * @var Data
     */
    private Data $helper;
    /**
     * @var Store
     */
    private Store $storeResourceModel;
    /**
     * @var WebsiteFactory
     */
    private WebsiteFactory $websiteFactory;
    /**
     * @var Website
     */
    private Website $websiteResourceModel;
    /**
     * @var StoreFactory
     */
    private StoreFactory $storeFactory;
    /**
     * @var GroupFactory
     */
    private GroupFactory $groupFactory;
    /**
     * @var Group
     */
    private Group $groupResourceModel;

    /**
     * CreateWebsite constructor.
     *
     * @param Store $storeResourceModel
     * @param WebsiteFactory $websiteFactory
     * @param Website $websiteResourceModel
     * @param StoreFactory $storeFactory
     * @param GroupFactory $groupFactory
     * @param Group $groupResourceModel
     * @param Data $helper
     */
    public function __construct(
        Store $storeResourceModel,
        WebsiteFactory $websiteFactory,
        Website $websiteResourceModel,
        StoreFactory $storeFactory,
        GroupFactory $groupFactory,
        Group $groupResourceModel,
        Data $helper
    ) {
        $this->storeResourceModel = $storeResourceModel;
        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->storeFactory = $storeFactory;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->helper = $helper;
    }

    /**
     * Patch to create websites
     */
    public function apply()
    {
        $this->createWebsite(
            'rvt_website',
            'Roseville Toyota',
            'rvt_store',
            'Roseville Toyota Store',
            'rvt_website_en',
            'Roseville Toyota En',
            'Roseville Category'
        );
        $this->createWebsite(
            'vct_website',
            'Vancouver Toyota',
            'vct_store',
            'Vancouver Toyota Store',
            'vct_website_en',
            'Vancouver Toyota En',
            'Vancouver Category'
        );
    }

    /**
     * @inheritdoc
     *
     * @return void
     * @throws \Exception
     */
    public function createWebsite(
        $website_code,
        $website_name,
        $store_code,
        $store_group_name,
        $store_view_code,
        $store_view_name,
        $root_category_name
    ) {
        
        $webCode = $website_code;
        $webName = $website_name;
        $storeCode = $store_code;
        $storeGroupName = $store_group_name;
        $storeViewCode = $store_view_code;
        $storeViewName = $store_view_name;
        $websiteDomain = '';
        $rootCategoryParentId = 1;
        $defaultGroupId = 0;
        $rootCategoryName = $root_category_name;

        /** Creating website */
        $website = $this->helper->createWebsite(
            $this->websiteFactory,
            $this->websiteResourceModel,
            $webCode,
            $webName,
            $defaultGroupId
        );

        /** Creating Root Category */
        $categoryId = $this->helper->createRootCategory(
            $rootCategoryParentId,
            $rootCategoryName
        );

        /** Creating Group */
        $groupId = $this->helper->createGroup(
            $website,
            $categoryId,
            $this->groupResourceModel,
            $this->groupFactory,
            $storeGroupName,
            $storeCode,
            $defaultGroupId
        );

        /** Creating Store */
        $this->helper->createStore(
            $this->storeFactory,
            $this->groupFactory,
            $this->storeResourceModel,
            $storeGroupName,
            $storeViewCode,
            $storeViewName,
            $webCode,
            $groupId
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
