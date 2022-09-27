<?php

/**
 * @package Infosys/ProductsByVin
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\ProductsByVin\Model\Resolver;

use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Layer\Resolver;
use Infosys\SearchByVIN\Model\Resolver\VehicleResolver;
use Infosys\ProductsByVin\Helper\Data as VinHelper;
use Infosys\Vehicle\Model\ResourceModel\Vehicle\Collection as VehicleCollection;
use Magento\Search\Model\QueryFactory;
use Psr\Log\LoggerInterface;
use Infosys\VehicleSearch\Helper\VehicleData;

/**
 * Products field resolver, used for GraphQL request processing.
 */
class Products implements ResolverInterface
{
    private ProductQueryInterface $searchQuery;
    private VinHelper $vinHelper;
    protected VehicleResolver $vehicleResolver;
    protected VehicleCollection $vehicleCollection;
    protected QueryFactory $queryFactory;
    protected LoggerInterface $logger;
    protected VehicleData $vehicleData;

    /**
     * Constructor function
     *
     * @param ProductQueryInterface $searchQuery
     * @param VinHelper $vinHelper
     * @param VehicleResolver $vehicleResolver
     * @param QueryFactory $queryFactory
     * @param VehicleCollection $vehicleCollection
     * @param LoggerInterface $logger
     * @param VehicleData $vehicleData
     */
    public function __construct(
        ProductQueryInterface $searchQuery,
        VinHelper $vinHelper,
        VehicleResolver $vehicleResolver,
        QueryFactory $queryFactory,
        VehicleCollection $vehicleCollection,
        LoggerInterface $logger,
        VehicleData $vehicleData
    ) {
        $this->searchQuery = $searchQuery;
        $this->vinHelper = $vinHelper;
        $this->vehicleResolver = $vehicleResolver;
        $this->queryFactory = $queryFactory;
        $this->vehicleCollection = $vehicleCollection;
        $this->logger = $logger;
        $this->vehicleData = $vehicleData;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->validateInput($args);

        //Fix for Part Number search & Special Characters
        if (isset($args['search']) && !preg_match('/^[^a-zA-Z0-9]+$/', $args['search'])) {
            if (strlen($args['search']) >= 9 && strlen($args['search']) <= 14) {
                $args['search'] = preg_replace('/[^a-zA-Z0-9\s]/', '', $args['search']);
                $args['search'] = preg_replace('/\s+/', ' ', $args['search']);
            } else {
                $args['search'] = preg_replace('/[^a-zA-Z0-9\s-]/', ' ', $args['search']);
                $args['search'] = preg_replace('/\s+/', ' ', $args['search']);
            }
        }

        $searchByVin = false;
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $dealer_brand = $this->vinHelper->getEnabledBrands($storeId);
        if (!$dealer_brand) {
            $message = "Brand is not selected for store with ID " . $storeId . ".  See Stores -> Configuration -> Toyota -> Dealer Brand -> Brand Configuration";
            $this->logger->warning($message);
            //TODO Refactor so search results can still be returned in this scenario.
            return [
                'error' => $message
            ];
        }
        $dealer_brand = explode(',', $dealer_brand);

        $searchByVinEnabled = $this->vinHelper->isSearchByVinEnabled();
        $isVinSearch = $searchByVinEnabled && isset($args['search']) && preg_match('/^[a-zA-Z0-9]{17}$/', $args['search']);

        if ($isVinSearch) {
            $attributes = $this->vehicleResolver->getAttributes($args['search']);
            if (isset($attributes['allRecords']) && isset($attributes['allRecords'][0]) && is_array($attributes['allRecords'][0])) {
                $model_year = $attributes['allRecords'][0]['model_year'];
                $model_code = $attributes['allRecords'][0]['model_code'];
                $vehicleAttributes = $this->getVehicleAttributes($model_year, $model_code);
                $brand = $vehicleAttributes['brand'];
                if (in_array($brand, $dealer_brand)) {
                    $searchByVin = true;
                    $filters = [
                        'model_year_code' => ['eq' => $model_year . ":" . $model_code],
                    ];
                    $vehicle_data = [
                        'entity_id' => $vehicleAttributes['entity_id'],
                        'model_year' => $model_year,
                        'model_code' => $model_code,
                        'series_name' => $vehicleAttributes['series_name'],
                        'grade' => $vehicleAttributes['grade'],
                        'driveline' => $vehicleAttributes['driveline'],
                        'body_style' => $vehicleAttributes['body_style'],
                        'vehicle_image' => $attributes['allRecords'][0]['vehicle_image']
                    ];
                    if (isset($args['filter'])) {
                        $args['filter'] = array_merge($args['filter'], $filters);
                    } else {
                        $args['filter'] = $filters;
                    }
                    unset($args['search']);
                }
            }
        }


        $modelYearCodeFilterOverrideEnabled = $this->vehicleData->isModelYearCodeOverride();
        $hasModelYearCodeFilter = $modelYearCodeFilterOverrideEnabled && isset($args['filter']) && isset($args['filter']['model_year_code']);

        if ($hasModelYearCodeFilter) {
            //if filters contain model_year_code, remove other vehicle filters then add them back in after we get our results
            //This fixes an issue where products don't show up on PLP pages with selected vehicle after vehicle data updates
            $vehicleFilters = [];
            foreach ($this->vehicleData->getVehicleAttributes() as $vehicleAttribute) {
                if ($vehicleAttribute != 'model_year_code' && isset($args['filter'][$vehicleAttribute])) {
                    //Save vehicle filters to add back later
                    $vehicleFilters[$vehicleAttribute] = $args['filter'][$vehicleAttribute];
                    //remove vehicle filters
                    unset($args['filter'][$vehicleAttribute]);
                }
            }
        }

        $args['filter']['brand'] = ['in' => $dealer_brand];

        $searchResult = $this->searchQuery->getResult($args, $info, $context);
        $searchTerms = [];

        $showSuggestedTerms = $this->vinHelper->isShowSuggestedTerms();
        if ($showSuggestedTerms && isset($args['search'])) {
            $query = $this->queryFactory->get()
                ->setQueryText($args['search'])
                ->setData('is_query_text_short', false);
            foreach ($query->getSuggestCollection() as $resultItem) {
                $searchTerms[] = $resultItem->getQueryText();
            }
            if ($searchResult->getTotalCount()) {
                $query->saveNumResults($searchResult->getTotalCount());
                $query->saveIncrementalPopularity();
            }
        }
        if ($searchResult->getCurrentPage() > $searchResult->getTotalPages() && $searchResult->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchResult->getCurrentPage(), $searchResult->getTotalPages()]
                )
            );
        }

        $data = [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $searchResult->getProductsSearchResult(),
            'page_info' => [
                'page_size' => $searchResult->getPageSize(),
                'current_page' => $searchResult->getCurrentPage(),
                'total_pages' => $searchResult->getTotalPages(),
            ],
            'search_result' => $searchResult,
            'layer_type' => isset($args['search']) ? Resolver::CATALOG_LAYER_SEARCH : Resolver::CATALOG_LAYER_CATEGORY,
            'search_terms' => $searchTerms,
            'inputs' => $args
        ];

        if (isset($args['filter']['category_id'])) {
            $data['categories'] = $args['filter']['category_id']['eq'] ?? $args['filter']['category_id']['in'];
            $data['categories'] = is_array($data['categories']) ? $data['categories'] : [$data['categories']];
        }

        if ($searchByVin) {
            $data['vehicle_details'] = [
                'entity_id' => $vehicle_data['entity_id'],
                'model_year' => $vehicle_data['model_year'],
                'model_code' => $vehicle_data['model_code'],
                'series_name' => $vehicle_data['series_name'],
                'grade' => $vehicle_data['grade'],
                'driveline' => $vehicle_data['driveline'],
                'body_style' => $vehicle_data['body_style'],
                'vehicle_image' => $vehicle_data['vehicle_image']
            ];
        }

        if ($hasModelYearCodeFilter) {
            //Add vehicle filters back in for response
            foreach ($vehicleFilters as $vehicleAttribute => $vehicleFilter) {
                $data['inputs']['filter'][$vehicleAttribute] = $vehicleFilter;
            }
        }

        return $data;
    }

    /**
     * Validate input arguments.
     *
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     */
    private function validateInput(array $args)
    {
        if (isset($args['searchAllowed']) && $args['searchAllowed'] === false) {
            throw new GraphQlAuthorizationException(__('Product search has been disabled.'));
        }
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        if (!isset($args['search']) && !isset($args['filter'])) {
            throw new GraphQlInputException(__("'search' or 'filter' input argument is required."));
        }
    }

    /**
     * Method to get vehicle series name and body style
     *
     * @param int $model_year
     * @param string $model_code
     * @return array
     */
    public function getVehicleAttributes($model_year, $model_code)
    {
        $attributesArray = [
            'entity_id' => '',
            'brand' => '',
            'series_name' => '',
            'grade' => '',
            'driveline' => '',
            'body_style' => ''
        ];
        $collection = $this->vehicleCollection->addFieldToSelect('*')
            ->addFieldToFilter('model_year', ['eq' => $model_year])
            ->addFieldToFilter('model_code', ['eq' => $model_code]);
        if (!empty($collection)) {
            $vehicle = $collection->getFirstItem();
            $attributesArray = [
                'entity_id' => $vehicle->getEntityId(),
                'brand' => $vehicle->getBrand(),
                'series_name' => $vehicle->getSeriesName(),
                'grade' => $vehicle->getGrade(),
                'driveline' => $vehicle->getDriveline(),
                'body_style' => $vehicle->getBodyStyle()
            ];
        }
        return $attributesArray;
    }
}
