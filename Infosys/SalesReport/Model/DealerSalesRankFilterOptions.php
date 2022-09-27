<?php
/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\SalesReport\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Infosys\CreateWebsite\Model\ResourceModel\TRD\CollectionFactory as TRDCollectionFactory;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Stdlib\Parameters;

/**
 * Class to provide filter options
 */
class DealerSalesRankFilterOptions 
{
    /**
     * @var ResourceConnection
    */
    private $resource;

    /**
     * @var ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
     * @var BackendSession
    */
    protected $backendSession;

    /**
     * @var TRDCollectionFactory
    */
    protected $tRDCollectionFactory;

    /**
     * @var WebsiteCollectionFactory
    */
    private $websiteCollection;
    
    /**
     * @var Brand Path
    */
    protected $brandPath = 'dealer_brand/brand_config/brand_filter';

    /**
     * @var DecoderInterface
    */
    private $urlDecoder;

    /**
     * @var RequestInterface
    */
    private $request;

    /**
     * @var Parameters
     */
    private $parameters;

    public function __construct(
        Session $backendSession,
        ScopeConfigInterface $scopeConfig,
        TRDCollectionFactory $tRDCollectionFactory,
        WebsiteCollectionFactory $websiteCollection,
        ResourceConnection $resource,
        DecoderInterface $urlDecoder,
        Parameters $parameters,
        RequestInterface $request
    )
    {
        $this->backendSession = $backendSession;
        $this->scopeConfig = $scopeConfig;
        $this->tRDCollectionFactory = $tRDCollectionFactory;
        $this->websiteCollection = $websiteCollection;
        $this->resource = $resource;
        $this->urlDecoder = $urlDecoder;
        $this->parameters = $parameters;
        $this->request = $request;
    }

    /**
    * Function to return all brands of websites as per the logged in user access
    * @return array
    */
    public function getBrand() : array
    {
        if($this->allWebsiteEnabled())
        {
            return $this->getDefaultBrands();
        }else
        {
            $websiteBrands = $temparr = [];
            $websiteIds = $this->getWebsiteIds();
            if(!empty($websiteIds))
            {
                foreach($websiteIds as $websiteId)
                {
                    $allBrands = explode(',',$this->getBrandConfig($websiteId));
                    foreach($allBrands as $singleBrand)
                    {
                        if(!in_array($singleBrand, $temparr))
                        {
                            $temparr[]=$singleBrand;
                            $websiteBrands[] = array('label'=>$singleBrand,'value'=>$singleBrand);
                        }
                    }
                }
            }
        }
        return $websiteBrands;
    }

    /**
    * Function to return all regions of websites as per the logged in user access
    * @return array
    */
    public function getRegion(): array
    {
        $region = $allRegionIds = [];
        $filterData = $this->filterData();
        if(isset($filterData['brand']))
        {
            $data = $this->getRegionAjax(implode(',',$filterData['brand']));
            if(isset($data['region']))
            {
                return $data['region'];
            }
        }
        if($this->allWebsiteEnabled())
        {
            $regionData = $this->websiteCollection->create()->addFieldToSelect('region_id')->distinct(true)->getData();
            if(!empty($regionData))
            {
                foreach($regionData as $singleRegion)
                {
                    $allRegionIds[] = $singleRegion['region_id'];
                }
            }
            $TRDcollection = $this->tRDCollectionFactory->create()
                ->addFieldToFilter('id', ['in' => $allRegionIds])
                ->addFieldToSelect('id')
                ->addFieldToSelect('region_label')
                ->getData();
            if(!empty($TRDcollection))
            {
                foreach($TRDcollection as $singleRegion)
                {
                    $region[$singleRegion['id']] = $singleRegion['region_label'];
                }
            }
        }
        else 
        {
            $websiteIds = $this->getWebsiteIds();
            if(count($websiteIds) > 0)
            {
                $regionData = $this->websiteCollection->create()->addFieldToFilter('website_id', ['in' => $websiteIds])->addFieldToSelect('region_id')->distinct(true)->getData();
                if(!empty($regionData))
                {
                    foreach($regionData as $singleRegion)
                    {
                        $allRegionIds[] = $singleRegion['region_id'];
                    }
                }
                $TRDcollection = $this->tRDCollectionFactory->create()
                    ->addFieldToFilter('id', ['in' => $allRegionIds])
                    ->addFieldToSelect('id')
                    ->addFieldToSelect('region_label')
                    ->getData();
                if(!empty($TRDcollection))
                {
                    foreach($TRDcollection as $singleRegion)
                    {
                        $region[$singleRegion['id']] = $singleRegion['region_label'];
                    }
                }
            }
        }
        return $region;
    }

    /**
    * Function to return all regions of websites as per the logged in user access and selected brands
    * @return array
    */
    public function getRegionAjax($brands=NULL): array
    {
        $requestedBrands = explode(',',$brands);
        $returnData = $selectedWebsiteIds = [];
        if($this->allWebsiteEnabled())
        {
            $websiteIds = $this->websiteCollection->create()->addFieldToSelect('website_id')->getData();
            foreach($websiteIds as $singleWebsite)
            {
                $websiteId = $singleWebsite['website_id'];
                $allBrands = explode(',',$this->getBrandConfig($websiteId));
                foreach($allBrands as $singleBrand)
                {
                    if(in_array($singleBrand, $requestedBrands))
                    {
                        $selectedWebsiteIds[] = $websiteId;
                    }
                }
            }
            $connection = $this->resource->getConnection();
            $sql = $connection->select()
                ->from(['tdr' => 'toyota_dealer_regions'])
                ->join(['sw' => 'store_website'], 'tdr.id = sw.region_id',['tdr.id','tdr.region_label'])
                ->where('sw.website_id IN (?)', $selectedWebsiteIds);
            $regionData = $connection->fetchAll($sql);
           
            if (!empty($regionData)) {
                foreach ($regionData as $singleRegion) {
                    $returnData['region'][$singleRegion['id']] = $singleRegion['region_label'];
                }
            }
        }
        else 
        {
            $websiteIds = $this->getWebsiteIds();
            if(count($websiteIds) > 0)
            {
                foreach($websiteIds as $singleWebsiteId)
                {
                    $allBrands = explode(',',$this->getBrandConfig($singleWebsiteId));
                    foreach($allBrands as $singleBrand)
                    {
                        if(in_array($singleBrand, $requestedBrands))
                        {
                            $selectedWebsiteIds[] = $singleWebsiteId;
                        }
                    }
                }
                $connection = $this->resource->getConnection();
                $sql = $connection->select()->from(['tdr' => 'toyota_dealer_regions'])
                    ->join(['sw' => 'store_website'], 'tdr.id = sw.region_id',['tdr.id','tdr.region_label'])
                    ->where('sw.website_id IN (?)', $selectedWebsiteIds);
                $regionData = $connection->fetchAll($sql);
                if (!empty($regionData)) {
                    foreach ($regionData as $singleRegion) {
                        $returnData['region'][$singleRegion['id']] = $singleRegion['region_label'];
                    }
                }
            }
        }
        if(isset($returnData['region']) && count($returnData['region']) >= 1)
        {
            $data['region'] = array_key_first($returnData['region']);
            $data['brands'] = $brands;
            $dealers = $this->getDealerAjax($data);
            $returnData['dealers'] = $dealers;
        }
        return $returnData;
    }

    /**
    * Function to return all websites as per the logged in user access
    * @return array
    */
    public function getDealer(): array
    {
        $websites = [];
        $filterData = $this->filterData();
        if(isset($filterData['brand']) && isset($filterData['region']))
        {
            $filterData['brands'] = $filterData['brand'][0];
            unset($filterData['brand']);
            $dealers = $this->getDealerAjax($filterData);
            if(count($dealers)>0)
            {
                return $dealers;
            }        
        }
        if($this->allWebsiteEnabled())
        {
            $websiteData = $this->websiteCollection->create()
                ->addFieldToSelect('website_id')
                ->addFieldToSelect('name')
                ->getData();
            foreach($websiteData as $singleWebsite)
            {
                $websites[$singleWebsite['website_id']] = $singleWebsite['name'];
            }
            return $websites;
        }
        else
        {
            $websiteData = $this->websiteCollection->create()
            ->addFieldToFilter('website_id', ['in' => $this->getWebsiteIds()])
            ->addFieldToSelect('website_id')
            ->addFieldToSelect('name')
            ->getData();
            foreach($websiteData as $singleWebsite)
            {
                $websites[$singleWebsite['website_id']] = $singleWebsite['name'];
            }
            return $websites;
        }
    }

    /**
    * Function to return all websites as per the logged in user access, selected brands and selected regions
    * @return array
    */
    public function getDealerAjax($data = NULL): array
    {
        $websites = $selectedWebsiteIds = [];
        $requestedBrands = explode(',',$data['brands']);
        $requestedRegion = $data['region'];
        if($this->allWebsiteEnabled())
        {
            $websiteIds = $this->websiteCollection->create()->addFieldToSelect('website_id')->getData();
            foreach($websiteIds as $singleWebsite)
            {
                $websiteId = $singleWebsite['website_id'];
                $allBrands = explode(',',$this->getBrandConfig($websiteId));
                foreach($allBrands as $singleBrand)
                {
                    if(in_array($singleBrand, $requestedBrands))
                    {
                        $selectedWebsiteIds[] = $websiteId;
                    }
                }
            }
            $connection = $this->resource->getConnection();
            $sql = $connection->select()
                ->from(['sw' => 'store_website'],['website_id','name'])
                ->where('sw.website_id IN (?)', $selectedWebsiteIds)
                ->where('sw.region_id = (?)', $requestedRegion);
            $websiteData = $connection->fetchAll($sql);
            if (!empty($websiteData)) {
                foreach ($websiteData as $singleWebsite) {
                    $websites[$singleWebsite['website_id']] = $singleWebsite['name'];
                }
            }
        }
        else
        {
            $websiteIds = $this->getWebsiteIds();
            if(!empty($websiteIds))
            {
                foreach($websiteIds as $singleWebsite)
                {
                    $allBrands = explode(',',$this->getBrandConfig($singleWebsite));
                    foreach($allBrands as $singleBrand)
                    {
                        if(in_array($singleBrand, $requestedBrands))
                        {
                            $selectedWebsiteIds[] = $singleWebsite;
                        }
                    }
                }
                $connection = $this->resource->getConnection();
                $sql = $connection->select()
                    ->from(['sw' => 'store_website'],['website_id','name'])
                    ->where('sw.website_id IN (?)', $selectedWebsiteIds)
                    ->where('sw.region_id = (?)', $requestedRegion);
                $websiteData = $connection->fetchAll($sql);
                if (!empty($websiteData)) {
                    foreach ($websiteData as $singleWebsite) {
                        $websites[$singleWebsite['website_id']] = $singleWebsite['name'];
                    }
                }
            }
        }
        return $websites;
    }

    /**
     * Check whether current user has all website access or not
     * @return bool
    */
    protected function allWebsiteEnabled() : bool
    {
        if($this->backendSession->getUser()->getData('all_website') ==1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    

    /**
     * Get all website Ids as per the logged in user access
     * @return array
    */
    protected function getWebsiteIds(): array
    {
        $websiteIds =[];
        $websitedata = $this->backendSession->getUser()->getData('website_ids');
        if(isset($websitedata))
        {
            $websiteIds = explode(',',$websitedata);
        }
        return $websiteIds;        
    }


    /**
     * Get store config value
     * @param int $websiteId
     * @return string
    */
    protected function getBrandConfig($websiteId = null) : string
    {
        return $this->scopeConfig->getValue(
            $this->brandPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get default website brands
     * @return string
    */
    protected function getDefaultBrands() : array
    {
        return [
            [
                'label' => 'TOYOTA',
                'value' => 'TOYOTA',
            ],
            [
                'label' => 'LEXUS',
                'value' => 'LEXUS',
            ],
            [
                'label' => 'SCION',
                'value' => 'SCION',
            ]
        ];
    }

    /*
    *Function to return user selected filter data
    */
    public function filterData()
    {
        $filter = $this->request->getParam('filter');
        if (null != $filter && is_string($filter)) {
            $filter = $this->urlDecoder->decode($filter);
            $this->parameters->fromString(urldecode($filter));
            $filterData = $this->parameters->toArray();
            return $filterData;
        }
        else
        {
            return null;
        }
    }
    
}
