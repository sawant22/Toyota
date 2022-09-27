<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver;

use Infosys\VehicleSearch\Model\Resolver\Vehicles\Query\Search;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Layer\Resolver;
use Infosys\VehicleSearch\Model\Resolver\Vehicles\Query\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Vehicles field resolver, used for GraphQL request processing.
 */
class Vehicles implements ResolverInterface
{
    /**
     * @var Search
     */
    private $searchQuery;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchApiCriteriaBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Search $searchQuery
     * @param LayerBuilder $layerBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchCriteriaBuilder|null $searchApiCriteriaBuilder
     */
    public function __construct(
        Search $searchQuery,
        LayerBuilder $layerBuilder,
        ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $searchApiCriteriaBuilder = null
    ) {
        $this->searchQuery = $searchQuery;
        $this->layerBuilder = $layerBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->searchApiCriteriaBuilder = $searchApiCriteriaBuilder ??
            \Magento\Framework\App\ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->validateInput($args);

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $brands = $this->getConfig('dealer_brand/brand_config/brand_filter', $storeId);

        if($brands){
            $brands = explode(',', $brands);
            $args['filter']['brand'] = ['eq' => $brands];
        }

        $searchResult = $this->searchQuery->getResult($args, $info, $context);

        $aggregations = $searchResult->getSearchAggregation();

        if ($aggregations) {
            $results = $this->layerBuilder->build($aggregations, $storeId);

            if ($results) {
                if (isset($results['model_year_bucket'])) {
                    rsort($results['model_year_bucket']['options'], SORT_REGULAR);
                }
                if (isset($results['series_name_bucket'])) {
                    sort($results['series_name_bucket']['options'], SORT_REGULAR);
                }
                if (isset($results['grade_bucket'])) {
                    sort($results['grade_bucket']['options'], SORT_REGULAR);
                }
                if (isset($results['driveline_bucket'])) {
                    sort($results['driveline_bucket']['options'], SORT_REGULAR);
                }
                if (isset($results['body_style_bucket'])) {
                    sort($results['body_style_bucket']['options'], SORT_REGULAR);
                }
                if (isset($results['engine_type_bucket'])) {
                    sort($results['engine_type_bucket']['options'], SORT_REGULAR);
                }
                if (isset($results['transmission_bucket'])) {
                    sort($results['transmission_bucket']['options'], SORT_REGULAR);
                }
                array_walk_recursive($results, function (&$v, $k) {
                    if ($k == 'attribute_code' || $k == 'label') {
                        $v = str_replace("_bucket", "", $v);
                    }
                });
            }

        }

        $data = [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $searchResult->getVehiclesSearchResult(),
            'page_info' => [
                'page_size' => $searchResult->getPageSize(),
                'current_page' => $searchResult->getCurrentPage(),
                'total_pages' => $searchResult->getTotalPages()
            ],
            'aggregations' => $results,
        ];

        return $data;
    }

    /**
     * Validate input arguments
     *
     * @param array $args
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     */
    private function validateInput(array $args)
    {
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
    }

    /**
     * Get store config value for brand
     *
     * @param sting $path
     * @param int $storeId
     * @return void
     */
    protected function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
