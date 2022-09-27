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
use Infosys\CreateWebsite\Model\TRDFactory;
use Infosys\CreateWebsite\Api\TRDRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

/**
 * Class to create websites
 */
class InstallRegions implements DataPatchInterface
{

    /**
     * @var TRDRepositoryInterface
     */
    protected $trdRepository;

    /**
     * @var TRDFactory
     */
    protected $trdFactory;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    protected $websiteCollectionFactory;

    /**
     *
     * @param TRDFactory $trdFactory
     * @param TRDRepositoryInterface $trdRepository
     */
    public function __construct(
        TRDRepositoryInterface $trdRepository,
        TRDFactory $trdFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $websiteCollectionFactory
    ) {
        $this->trdRepository = $trdRepository;
        $this->trdFactory = $trdFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
    }

    /**
     * Patch to create websites
     */
    public function apply()
    {
        $regions = [
            //Toyota areas
            ['region_code' => '11', 'region_label' => 'Los Angeles'],
            ['region_code' => '12', 'region_label' => 'San Francisco'],
            ['region_code' => '13', 'region_label' => 'Portland'],
            ['region_code' => '15', 'region_label' => 'Denver'],
            ['region_code' => '16', 'region_label' => 'New York'],
            ['region_code' => '17', 'region_label' => 'Boston'],
            ['region_code' => '21', 'region_label' => 'Chicago'],
            ['region_code' => '22', 'region_label' => 'Cincinnati'],
            ['region_code' => '23', 'region_label' => 'Kansas City'],
            ['region_code' => '50', 'region_label' => 'Southeast Toyota'],
            ['region_code' => '60', 'region_label' => 'Gulf States Toyota'],
            ['region_code' => '80', 'region_label' => 'Central Atlantic Toyota'],
            // Lexus Areas
            ['region_code' => '31', 'region_label' => 'Western'],
            ['region_code' => '32', 'region_label' => 'Central'],
            ['region_code' => '33', 'region_label' => 'Eastern'],
            ['region_code' => '34', 'region_label' => 'Southern']
        ];

        foreach ($regions as $region) {
            $this->createRegion($region);
        }

        $storeWebsiteRegions = [
            'dealer_09190' => '50',
            'dealer_42253' => '60',
            'dealer_04554' => '11',
            'dealer_34089' => '22',
            'dealer_34085' => '22',
            'dealer_15010' => '23',
            'dealer_24045' => '23',
            'dealer_32111' => '50',
            'dealer_32141' => '50',
            'dealer_04536' => '11',
            'dealer_16051' => '22',
            'dealer_04421' => '12',
            'dealer_05034' => '15',
            'dealer_14044' => '23',
            'dealer_04543' => '11',
            'dealer_46071' => '13'];

        $storeWebsites = $this->websiteCollectionFactory->create();
        foreach ( $storeWebsites as $storeWebsite ) {
            if (isset($storeWebsiteRegions[$storeWebsite->getCode()])){

                $filter = $this->filterBuilder
                    ->setField('region_code')
                    ->setConditionType('eq')
                    ->setValue($storeWebsiteRegions[$storeWebsite->getCode()])
                    ->create();

                $this->searchCriteriaBuilder->addFilters([$filter]);
                $searchCriteria = $this->searchCriteriaBuilder->create();
                $modelRegion = $this->trdRepository->getList($searchCriteria)->getItems();
                $modelRegion = array_pop($modelRegion);

                $storeWebsite->setRegionId($modelRegion->getId());
                $storeWebsite->save();
            }
        }

    }

    /**
     * Method to create new $region
     *
     * @param array $region
     * @return void
     */
    public function createRegion($region)
    {
        $newTRD = $this->trdFactory->create();
        $newTRD->setRegionCode($region['region_code']);
        $newTRD->setRegionLabel($region['region_label']);
        $this->trdRepository->save($newTRD);
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
