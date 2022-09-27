<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver\Vehicles\Query;

use Infosys\VehicleSearch\Model\Resolver\Vehicles\Query\SearchCriteriaBuilder;
use Infosys\VehicleSearch\Model\Resolver\Vehicles\DataProvider\VehicleSearch;
use Infosys\VehicleSearch\Model\Resolver\Vehicles\Query\SearchResultFactory;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Search\Api\SearchInterface;
use Magento\Search\Model\Search\PageSizeProvider;
use Infosys\YMMSearchGraphQL\Model\EFCapi;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManager;

/**
 * Full text search for vehicles using given search criteria.
 */
class Search
{
    const VEHICLE_NAME = null;
    const VEHICLE_IMAGE = null;

    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var PageSizeProvider
     */
    private $pageSizeProvider;

    /**
     * @var FieldSelection
     */
    private $fieldSelection;

    /**
     * @var ArgumentsProcessorInterface
     */
    private $argsSelection;

    /**
     * @var VehicleSearch
     */
    private $vehiclesProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Infosys\YMMSearchGraphQL\Model\EFCapi
     */
    protected $EFCapi;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @param SearchInterface $search
     * @param SearchResultFactory $searchResultFactory
     * @param PageSizeProvider $pageSize
     * @param FieldSelection $fieldSelection
     * @param VehicleSearch $vehiclesProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EFCapi $EFCapi
     * @param ArgumentsProcessorInterface|null $argsSelection
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManager $storeManager
     */
    public function __construct(
        SearchInterface $search,
        SearchResultFactory $searchResultFactory,
        PageSizeProvider $pageSize,
        FieldSelection $fieldSelection,
        VehicleSearch $vehiclesProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EFCapi $EFCapi,
        ArgumentsProcessorInterface $argsSelection = null,
        ScopeConfigInterface $scopeConfig,
        StoreManager $storeManager
    ) {
        $this->search = $search;
        $this->searchResultFactory = $searchResultFactory;
        $this->pageSizeProvider = $pageSize;
        $this->fieldSelection = $fieldSelection;
        $this->vehiclesProvider = $vehiclesProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->EFCapi = $EFCapi;
        $this->argsSelection = $argsSelection ?: ObjectManager::getInstance()
            ->get(ArgumentsProcessorInterface::class);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Return vehicle search results using Search API
     *
     * @param array $args
     * @param ResolveInfo $info
     * @param ContextInterface $context
     * @return SearchResult
     * @throws GraphQlInputException
     */
    public function getResult(
        array $args,
        ResolveInfo $info,
        ContextInterface $context
    ): SearchResult {
        
        $searchCriteria = $this->buildSearchCriteria($args, $info);
        $realPageSize = $searchCriteria->getPageSize();

        $realCurrentPage = $searchCriteria->getCurrentPage();
        
        $pageSize = $this->pageSizeProvider->getMaxPageSize();
        $searchCriteria->setPageSize($pageSize);
        $searchCriteria->setCurrentPage(0);
 
        $itemsResults = $this->search->search($searchCriteria);
        
        //Address limitations of sort and pagination on search API apply original pagination from GQL query
        $searchCriteria->setPageSize($realPageSize);
        $searchCriteria->setCurrentPage($realCurrentPage);
        
        $searchResults = $this->vehiclesProvider->getList(
            $searchCriteria,
            $itemsResults,
            $this->fieldSelection->getVehiclesFieldSelection($info),
            $context
        );
        
        $totalPages = $realPageSize ? ((int)ceil($searchResults->getTotalCount() / $realPageSize)) : 0;
        $totalCount = $itemsResults->getTotalCount();
        
        $vehicleArray = [];
        $vehicle_image = self::VEHICLE_IMAGE;
        $vehicle_name = self::VEHICLE_NAME;

        //placeholder image for vehicle
        $vehicle_placeholder_image = $this->scopeConfig->getValue(
            'epc_config/vehicle_placeholder/placeholder',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        foreach ($searchResults->getItems() as $vehicle) {
            $vehicle_data = $vehicle->getData();
            $vehicleArray[$vehicle->getId()] = $vehicle_data;

            //fetch vehicle image using EFC api
            if (isset($args['filter']['model_year']) && isset($args['filter']['series_name']) &&
                isset($args['filter']['driveline']) && isset($args['filter']['grade'])) {
                $res = $this->EFCapi->getVehicleImage($args['filter']['model_year']['eq'], $vehicle_data['model_code']);
                
                if($vehicle_placeholder_image){
                    $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
                    $vehicle_placeholder_image = $mediaUrl.'catalog/vehicle/placeholder/'.$vehicle_placeholder_image;
                    $vehicle_image = $vehicle_placeholder_image;
                }

                if (isset($res['years_list'])) {
                    if (isset($res['years_list'][0]['series_list'][0]['trims'][0]['images']) &&
                        isset($res['years_list'][0]['series_list'][0]['trims'][0]['images']['grade_car_jelly_image'])
                    ) {
                        $image = $res['years_list'][0]['series_list'][0]['trims'][0]['images']['grade_car_jelly_image'];
						$vehicle_name = $res['years_list'][0]['series_list'][0]['series'];
                        if ($image) {
                            $vehicle_image = $res['imageurl'] . $image;
                        }
                    }
                }
                $vehicleArray[$vehicle->getId()]['vehicle_name'] = $vehicle_name;
                $vehicleArray[$vehicle->getId()]['vehicle_image'] = $vehicle_image;

                //Return first vehicle in case of multiple vehicles
                $totalCount = 1;
                break;
            }
        }

        return $this->searchResultFactory->create(
            [
                'totalCount' => $totalCount,
                'vehiclesSearchResult' => $vehicleArray,
                'searchAggregation' => $itemsResults->getAggregations(),
                'pageSize' => $realPageSize,
                'currentPage' => $realCurrentPage,
                'totalPages' => $totalPages,
            ]
        );
    }

    /**
     * Build search criteria from query input args
     *
     * @param array $args
     * @param ResolveInfo $info
     * @return SearchCriteriaInterface
     */
    private function buildSearchCriteria(array $args, ResolveInfo $info): SearchCriteriaInterface
    {
        $vehicleFields = (array)$info->getFieldSelection(1);
        $includeAggregations = isset($vehicleFields['filters']) || isset($vehicleFields['aggregations']);
        $processedArgs = $this->argsSelection->process((string) $info->fieldName, $args);

        $searchCriteria = $this->searchCriteriaBuilder->build($processedArgs, $includeAggregations);

        return $searchCriteria;
    }
}
