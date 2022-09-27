<?php
/**
 * @package     Infosys/CreateWebsite
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare (strict_types = 1);

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
 * Class to create new websites
 */
class CreateNewWebsite implements DataPatchInterface
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
     * Constructor Function
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
        $dealers = [
            [
             'websitecode' => 'dealer_34089',
             'websitename' => 'Joseph Toyota of Cincinnati',
             'storecode' => 'jtc_store',
             'storename' => 'Joseph Toyota of Cincinnati Store',
             'storeviewcode' => 'jtc_website_en',
             'storeviewname' => 'Joseph Toyota of Cincinnati En',
             'rootcategoryname' => 'Joseph Category',
             'websiteurl' => 'https://www.josephtoyota.com/',
             'dealercode' => '34089',
             'regioncode' => '22'
            ],
            
            [
             'websitecode' => 'dealer_34085',
             'websitename' => 'Kings Toyota',
             'storecode' => 'kt_store',
             'storename' => 'Kings Toyota Store',
             'storeviewcode' => 'kt_website_en',
             'storeviewname' => 'Kings Toyota En',
             'rootcategoryname' => 'Kings Category',
             'websiteurl' => 'https://www.kingstoyota.com/',
             'dealercode' => '34085',
             'regioncode' => '22'
            ],

            [
             'websitecode' => 'dealer_16051',
             'websitename' => 'Oxmoor Toyota',
             'storecode' => 'ot_store',
             'storename' => 'Oxmoor Toyota Store',
             'storeviewcode' => 'ot_website_en',
             'storeviewname' => 'Oxmoor Toyota En',
             'rootcategoryname' => 'Oxmoor Category',
             'websiteurl' => 'https://www.oxmoortoyota.com/',
             'dealercode' => '16051',
             'regioncode' => '22'
            ],
            
            [
             'websitecode' => 'dealer_15010',
             'websitename' => 'Lewis Toyota of Topeka',
             'storecode' => 'ltt_store',
             'storename' => 'Lewis Toyota of Topeka Store',
             'storeviewcode' => 'ltt_website_en',
             'storeviewname' => 'Lewis Toyota of Topeka En',
             'rootcategoryname' => 'Lewis Category',
             'websiteurl' => 'https://www.lewistoyota.com/',
             'dealercode' => '15010',
             'regioncode' => '23'
            ],
            
            [
             'websitecode' => 'dealer_14044',
             'websitename' => 'Toyota of Des Moines',
             'storecode' => 'tdm_store',
             'storename' => 'Toyota of Des Moines Store',
             'storeviewcode' => 'tdm_website_en',
             'storeviewname' => 'Toyota of Des Moines En',
             'rootcategoryname' => 'Des Moines Category',
             'websiteurl' => 'https://www.toyotadm.com/',
             'dealercode' => '14044',
             'regioncode' => '23'
            ],
            
            [
             'websitecode' => 'dealer_24045',
             'websitename' => 'Lou Fusz Toyota',
             'storecode' => 'lft_store',
             'storename' => 'Lou Fusz Toyota Store',
             'storeviewcode' => 'lft_website_en',
             'storeviewname' => 'Lou Fusz Toyota En',
             'rootcategoryname' => 'Lou Fusz Category',
             'websiteurl' => 'https://www.fusztoyota.com/',
             'dealercode' => '24045',
             'regioncode' => '23'
            ],
            
            [
             'websitecode' => 'dealer_04543',
             'websitename' => 'Tustin Toyota',
             'storecode' => 'tt_store',
             'storename' => 'Tustin Toyota Store',
             'storeviewcode' => 'tt_website_en',
             'storeviewname' => 'Tustin Toyota En',
             'rootcategoryname' => 'Tustin Category',
             'websiteurl' => 'https://www.tustintoyota.com/',
             'dealercode' => '04543',
             'regioncode' => '11'
            ],
            
            [
             'websitecode' => 'dealer_04554',
             'websitename' => 'John Elways Crown Toyota',
             'storecode' => 'ject_store',
             'storename' => 'John Elways Crown Toyota Store',
             'storeviewcode' => 'ject_website_en',
             'storeviewname' => 'John Elways Crown Toyota En',
             'rootcategoryname' => 'John Elways Crown Category',
             'websiteurl' => 'https://www.crowntoyota.com/',
             'dealercode' => '04554',
             'regioncode' => '11'
            ],
            
            [
             'websitecode' => 'dealer_05034',
             'websitename' => 'Stevinson Toyota West',
             'storecode' => 'stw_store',
             'storename' => 'Stevinson Toyota West Store',
             'storeviewcode' => 'stw_website_en',
             'storeviewname' => 'Stevinson Toyota West En',
             'rootcategoryname' => 'Stevinson Category',
             'websiteurl' => 'https://www.stevinsontoyotawest.com/',
             'dealercode' => '05034',
             'regioncode' => '15'
            ],
            
            [
             'websitecode' => 'dealer_09190',
             'websitename' => 'Daytona Toyota',
             'storecode' => 'dt_store',
             'storename' => 'Daytona Toyota Store',
             'storeviewcode' => 'dt_website_en',
             'storeviewname' => 'Daytona Toyota En',
             'rootcategoryname' => 'Daytona Category',
             'websiteurl' => 'https://www.daytonatoyota.com/',
             'dealercode' => '09190',
             'regioncode' => '50'
            ],
            
            [
             'websitecode' => 'dealer_32111',
             'websitename' => 'Modern Toyota',
             'storecode' => 'mt_store',
             'storename' => 'Modern Toyota Store',
             'storeviewcode' => 'mt_website_en',
             'storeviewname' => 'Modern Toyota En',
             'rootcategoryname' => 'Modern Category',
             'websiteurl' => 'https://www.moderntoyota.com/',
             'dealercode' => '32111',
             'regioncode' => '50'
            ],
            
            [
             'websitecode' => 'dealer_32141',
             'websitename' => 'Modern Toyota of Boone',
             'storecode' => 'mtb_store',
             'storename' => 'Modern Toyota of Boone Store',
             'storeviewcode' => 'mtb_website_en',
             'storeviewname' => 'Modern Toyota of Boone En',
             'rootcategoryname' => 'Modern Boone Category',
             'websiteurl' => 'https://www.moderntoyotaofboone.com/',
             'dealercode' => '32141',
             'regioncode' => '50'
            ],
            
            [
             'websitecode' => 'dealer_42253',
             'websitename' => 'Freeman Toyota',
             'storecode' => 'ft_store',
             'storename' => 'Freeman Toyota Store',
             'storeviewcode' => 'ft_website_en',
             'storeviewname' => 'Freeman Toyota En',
             'rootcategoryname' => 'Freeman Category',
             'websiteurl' => 'https://www.freemantoyota.com/',
             'dealercode' => '42253',
             'regioncode' => '60'
            ]
        ];

        foreach ($dealers as $dealer) {
            $this->createWebsite($dealer);
        }

        /** Updating Websites */
        $this->helper->updateWebsite($this->websiteResourceModel);
    }

    /**
     * Method to create new dealers
     *
     * @param array $dealer
     * @return void
     */
    public function createWebsite($dealer)
    {
        $webCode = $dealer['websitecode'];
        $webName = $dealer['websitename'];
        $storeCode = $dealer['storecode'];
        $storeGroupName = $dealer['storename'];
        $storeViewCode = $dealer['storeviewcode'];
        $storeViewName = $dealer['storeviewname'];
        $websiteDomain = '';
        $rootCategoryParentId = 1;
        $defaultGroupId = 0;
        $rootCategoryName = $dealer['rootcategoryname'];
        $websiteUrl = $dealer['websiteurl'];
        $dealerCode = $dealer['dealercode'];
        $regionCode = $dealer['regioncode'];
        
        /** Creating website */
        $website = $this->helper->createWebsite(
            $this->websiteFactory,
            $this->websiteResourceModel,
            $webCode,
            $webName,
            $defaultGroupId,
            $dealerCode,
            $regionCode
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

        /** Creating Set Url */
        $this->helper->setUrl(
            $website,
            $websiteUrl
        );
    }

    /**
     * Get Dependencies
     *
     * @return void
     */
    public static function getDependencies()
    {
        return [CreateWebsite::class];
    }

    /**
     * Get Dependencies
     *
     * @return void
     */
    public function getAliases()
    {
        return [];
    }
}
