<?php

/**
 * @package     Infosys/CreateWebsite
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CreateWebsite\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\WebsiteFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class to create new website
 */
class Data extends AbstractHelper
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var storeFactory
     */
    private $storeFactory;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryInterface;
    /**
     * @var WebsiteFactory
     */
    private $webFactory;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Creating constructor.
     *
     * @param CategoryFactory $categoryFactory
     * @param ManagerInterface $eventManager
     * @param storeFactory $storeFactory
     * @param WebsiteFactory $webFactory
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        ManagerInterface $eventManager,
        storeFactory $storeFactory,
        WebsiteFactory $webFactory,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->eventManager = $eventManager;
        $this->storeFactory = $storeFactory;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->webFactory = $webFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Creating website
     *
     * @param WebsiteFactory $websiteFactory
     * @param Website $websiteResourceModel
     * @param String $websiteCode
     * @param String $websiteName
     * @param String $defaultGroupId
     * @param String $dealerCode
     * @param String $regionCode
     * @return array
     */
    public function createWebsite(
        $websiteFactory,
        $websiteResourceModel,
        $websiteCode,
        $websiteName,
        $defaultGroupId,
        $dealerCode = null,
        $regionCode = null
    ) {
        $website = $websiteFactory->create();
        $website->setCode($websiteCode);
        $website->setName($websiteName);
        $website->setDealerCode($dealerCode);
        $website->setRegionCode($regionCode);
        $website->setDefaultGroupId($defaultGroupId);
        $websiteResourceModel->save($website);
        return $website;
    }

    /**
     * Get Category Collection
     *
     * @param String $rootCategoryName
     * @return array
     */
    public function getCategoryCollection($rootCategoryName)
    {
        $collection = $this->categoryFactory->create()->getCollection()
            ->addAttributeToFilter('name', $rootCategoryName)->setPageSize(1);
        return $collection;
    }

    /**
     * Creating Root Category
     *
     * @param String $rootCategoryParentId
     * @param String $rootCategoryName
     * @return String
     */
    public function createRootCategory($rootCategoryParentId, $rootCategoryName)
    {
        $category = $this->categoryFactory->create();
        $category->setName($rootCategoryName);
        $category->setData('level', 1);
        $category->setParentId($rootCategoryParentId); // 1: root category.
        $category->setIsActive(true);
        $newCategory = $this->categoryRepositoryInterface->save($category);
        return $newCategory->getId();
    }

    /**
     * Creating Group
     *
     * @param object $website
     * @param String $categoryId
     * @param Group $groupResourceModel
     * @param GroupFactory $groupFactory
     * @param String $storeNameGroup
     * @param String $storeCode
     * @param String $defaultGroupId
     * @return String
     */
    public function createGroup(
        $website,
        $categoryId,
        $groupResourceModel,
        $groupFactory,
        $storeNameGroup,
        $storeCode,
        $defaultGroupId
    ) {
        $group = $groupFactory->create();
        $group->setWebsiteId($website->getWebsiteId());
        $group->setName($storeNameGroup);
        $group->setCode($storeCode);
        $group->setRootCategoryId($categoryId);
        $groupResourceModel->save($group);
        $newGroup = $groupFactory->create();
        $id = $newGroup->getCollection()->addFieldToFilter('code', ['eq' => "$storeCode"]);
        return $id->getData()[0]['group_id'];
    }

    /**
     * Creating Store
     *
     * @param StoreFactory $storeFactory
     * @param GroupFactory $groupFactory
     * @param Store $storeResourceModel
     * @param String $storeGroupName
     * @param String $storeViewCode
     * @param String $storeViewName
     * @param String $websiteCode
     * @param String $groupId
     * @return void
     */
    public function createStore(
        $storeFactory,
        $groupFactory,
        $storeResourceModel,
        $storeGroupName,
        $storeViewCode,
        $storeViewName,
        $websiteCode,
        $groupId
    ) {
        $website = $this->webFactory->create();
        $website->load($websiteCode);

        $store = $storeFactory->create();
        ;
        $store->setCode($storeViewCode);
        $store->setName($storeViewName);
        $store->setWebsite($website);
        $store->setGroupId($groupId);
        $store->setData('is_active', '1');
        $storeResourceModel->save($store);
    }

    /**
     * Creting Url for Websites
     *
     * @param object $website
     * @param String $websiteUrl
     * @return void
     */
    public function setUrl($website, $websiteUrl)
    {
        $websiteId = $website->getWebsiteId();
        $this->config->saveConfig('web/unsecure/base_url', $websiteUrl, 'websites', $websiteId);
        $this->config->saveConfig(
            'web/unsecure/base_link_url',
            $websiteUrl,
            'websites',
            $websiteId
        );
    }

    /**
     * Updating Websites
     *
     * @param Website $websiteResourceModel
     * @return void
     */
    public function updateWebsite($websiteResourceModel)
    {
        $arr = [
            [
                'oldcode' => 'rvt_website',
                'newcode' => 'dealer_04421',
                'dealercode' => '04421',
                'regioncode' => '12',
                'url' => 'https://www.rosevilletoyota.com/'
            ],
            [
                'oldcode' => 'vct_website',
                'newcode' => 'dealer_46071',
                'dealercode' => '46071',
                'regioncode' => '13',
                'url' => 'https://www.vancouvertoyota.com/'
            ]
        ];

        foreach ($arr as $data) {
            $website = $this->webFactory->create();
            $website->load($data['oldcode']);
            $website->setCode($data['newcode']);
            $website->setDealerCode($data['dealercode']);
            $website->setRegionCode($data['regioncode']);
            $websiteResourceModel->save($website);

            $websiteId = $website->getId();
            $storeMainUrl = $data['url'];
            $this->config->saveConfig('web/unsecure/base_url', $storeMainUrl, 'websites', $websiteId);
            $this->config->saveConfig(
                'web/unsecure/base_link_url',
                $storeMainUrl,
                'websites',
                $websiteId
            );
        }
    }
}
