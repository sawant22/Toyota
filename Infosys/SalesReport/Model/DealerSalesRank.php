<?php

/**
 * @package   Infosys/SalesReport
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Model;

use Magento\Framework\App\ResourceConnection;
use Infosys\SalesReport\Logger\SalesReportLogger;
use Infosys\SalesReport\Model\DealerSalesStatisticsFactory;
use Infosys\SalesReport\Model\SalesReportStoreConfig;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Stdlib\Parameters;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

/**
 * Class to calculate dealer rank performance
 */
class DealerSalesRank
{
    const MEDIUM_INDEX_DATATYPE = 'integer';

    protected ResourceConnection $resource;

    protected SalesReportLogger $logger;

    protected DealerSalesStatisticsFactory $dssFactory;

    protected SalesReportStoreConfig $configData;

    protected WebsiteFactory $websiteFactory;

    protected StoreManagerInterface $storeManager;

    private DecoderInterface $urlDecoder;

    private RequestInterface $request;

    private Parameters $parameters;

    private DateTimeFactory $dateTimeFactory;

    protected PricingHelper $priceHelper;


    /**
     * Constructor function
     *
     * @param ResourceConnection $resource
     * @param SalesReportLogger $logger
     * @param DealerSalesStatisticsFactory $dssFactory
     * @param SalesReportStoreConfig $configData
     * @param WebsiteFactory $websiteFactory
     * @param StoreManagerInterface $storeManager
     * @param DecoderInterface $urlDecoder
     * @param Parameters $parameters
     * @param RequestInterface $request
     * @param DateTimeFactory $dateTimeFactory
     * @param PricingHelper $priceHelper
     */
    public function __construct(
        ResourceConnection $resource,
        SalesReportLogger $logger,
        DealerSalesStatisticsFactory $dssFactory,
        SalesReportStoreConfig $configData,
        WebsiteFactory $websiteFactory,
        StoreManagerInterface $storeManager,
        DecoderInterface $urlDecoder,
        Parameters $parameters,
        RequestInterface $request,
        DateTimeFactory $dateTimeFactory,
        PricingHelper $priceHelper
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
        $this->dssFactory = $dssFactory;
        $this->configData = $configData;
        $this->websiteFactory = $websiteFactory;
        $this->storeManager = $storeManager;
        $this->urlDecoder = $urlDecoder;
        $this->parameters = $parameters;
        $this->request = $request;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Method to get applied filters data
     *
     * @return void
     */
    public function filterData()
    {
        $filter = $this->request->getParam('filter');
        if (null != $filter && is_string($filter)) {
            $filter = $this->urlDecoder->decode($filter);
            $this->parameters->fromString(urldecode($filter));
            $filterData = $this->parameters->toArray();
            if (isset($filterData['from']) || isset($filterData['to'])) {
                $dateModel = $this->dateTimeFactory->create();
            }
            if (isset($filterData['from'])) {
                $filterData['from'] = $dateModel->date('Y-m-d', $filterData['from']);
            }
            if (isset($filterData['to'])) {
                $filterData['to'] = $dateModel->date('Y-m-d', $filterData['to']);
            }
            return $filterData;
        } else {
            return null;
        }
    }

    /**
     * Method to calculate dealer rank
     *
     * @return void
     */
    public function calculateDealerRank()
    {
        try {
            $filterData = $this->filterData();
            $storeId = $regionId = $fromDate = $toDate = '';
            $storeIds = $statistics = $result = [];
            $result['filter'] = 0;
            $result['dealerName'] = '';
            if ($filterData) {
                $result['filter'] = 1;
                if (isset($filterData['dealer'])) {
                    $storeId = $filterData['dealer'];
                    $result['dealerName'] = $this->getDealerName($storeId);
                }
                if (isset($filterData['region'])) {
                    $regionId = $filterData['region'];
                }
                if (isset($filterData['from'])) {
                    $fromDate = $filterData['from'];
                }
                if (isset($filterData['to'])) {
                    $toDate = $filterData['to'];
                }

                $websiteCollection = $this->websiteFactory->create();
                $websiteCollection = $websiteCollection->getCollection()->addFieldToSelect('website_id');
                $websiteCollection->getSelect()->where('region_id IN (?)', $regionId);
                $websiteCollection = $websiteCollection->getData();  
                $storeIds = array_column($websiteCollection,'website_id');

                if ($fromDate != '' && $toDate != '') {
                    $dss = $this->dssFactory->create();
                    $dssCollection = $dss->getCollection()->addFieldToSelect('store_id');
                    $dssCollection->getSelect()->columns(
                        [
                            'orders_qty' => 'SUM(orders_qty)',
                            'product_sales' => 'SUM(product_sales)',
                            'percent_parts' => 'AVG(percent_parts)',
                            'percent_accessories' => 'AVG(percent_accessories)',
                            'shipping_sales' => 'SUM(shipping_sales)',
                            'total_net_sales' => 'SUM(total_net_sales)',
                            'total_gross_sales' => 'SUM(total_gross_sales)',
                            'product_gross_profit' => 'SUM(product_gross_profit)',
                            'shipping_gross_profit' => 'SUM(shipping_gross_profit)',
                            'total_gross_profit' => 'SUM(total_gross_profit)',
                            'gross_profit_per_order' => 'AVG(gross_profit_per_order)',
                            'product_gross_profit_percent' => 'AVG(product_gross_profit_percent)',
                            'total_gross_profit_percent' => 'AVG(total_gross_profit_percent)',
                            'total_discount' => 'SUM(total_discount)',
                            'time_to_ship' => 'AVG(time_to_ship)'
                        ]
                    )->where('store_id IN (?)', $storeIds)
                        ->where('report_date >= (?)', $fromDate)
                        ->where('report_date <= (?)', $toDate)
                        ->where('orders_qty > (?)',0)
                        ->group('store_id');
                    $statistics = $dssCollection->getData();
                } else {
                    $dss = $this->dssFactory->create();
                    $dssCollection = $dss->getCollection()->addFieldToSelect('store_id');
                    $dssCollection->getSelect()->columns(
                        [
                            'orders_qty' => 'SUM(orders_qty)',
                            'product_sales' => 'SUM(product_sales)',
                            'percent_parts' => 'AVG(percent_parts)',
                            'percent_accessories' => 'AVG(percent_accessories)',
                            'shipping_sales' => 'SUM(shipping_sales)',
                            'total_net_sales' => 'SUM(total_net_sales)',
                            'total_gross_sales' => 'SUM(total_gross_sales)',
                            'product_gross_profit' => 'SUM(product_gross_profit)',
                            'shipping_gross_profit' => 'SUM(shipping_gross_profit)',
                            'total_gross_profit' => 'SUM(total_gross_profit)',
                            'gross_profit_per_order' => 'AVG(gross_profit_per_order)',
                            'product_gross_profit_percent' => 'AVG(product_gross_profit_percent)',
                            'total_gross_profit_percent' => 'AVG(total_gross_profit_percent)',
                            'total_discount' => 'SUM(total_discount)',
                            'time_to_ship' => 'AVG(time_to_ship)'
                        ]
                    )->where('store_id IN (?)', $storeIds)
                        ->where('report_date BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()')
                        ->where('orders_qty > (?)',0)
                        ->group('store_id');
                    $statistics = $dssCollection->getData();
                }
                if (count($statistics) > 1) {
                    foreach ($this->configData->getDealerRankMetrics() as $metric => $metricValue) {
                        $result['data'][] = $this->calculateMetricRank($metric, $storeId, $statistics, $metricValue);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("Error while generating dealer rank report " . $e->getMessage());
        }
        return $result;
    }

    /**
     * Method to calculate dealer rank performace
     *
     * @param string $metric
     * @param int $storeId
     * @param array $statistics
     * @param array $metricValue
     * @return array
     */
    public function calculateMetricRank($metric, $storeId, $statistics, $metricValue): array
    {
        $result = [];
        $dealerPerformance = 0;
        foreach ($statistics as $object) {
            if ($object['store_id'] == $storeId) {
                $dealerPerformance = $object[$metric];
                break;
            }
        }
        $metricValues = array_sort_recursive(array_column($statistics, $metric));
        $high = $metricValues[count($metricValues) - 1];
        $low = $metricValues[0];
        $index = count($metricValues) / 2;
        $medIndex = gettype($index) == self::MEDIUM_INDEX_DATATYPE ? $index : floor($index);
        $medium = isset($metricValues[$medIndex]) ? $metricValues[$medIndex] : $metricValues[0];
        $rankArray = array_values(array_unique($metricValues));
        $rankIndex = count($rankArray);
        $rank = array_search($dealerPerformance, $rankArray) + 1;
        $finalRank =  $rank . '/' . $rankIndex;
        if (empty($metricValue[1])) {
            $result = [
                'metric' => $metric,
                'metric_label' => $metricValue[0],
                'your_performance' => round($dealerPerformance, 2),
                'low_dealer' => round($low, 2),
                'medium_dealer' => round($medium, 2),
                'high_dealer' => round($high, 2),
                'dealer_rank' => $finalRank,
            ];
        } else {
            $result = [
                'metric' => $metric,
                'metric_label' => $metricValue[0],
                'your_performance' => $this->getMetricCurrencyFormat($dealerPerformance),
                'low_dealer' => $this->getMetricCurrencyFormat($low),
                'medium_dealer' => $this->getMetricCurrencyFormat($medium),
                'high_dealer' => $this->getMetricCurrencyFormat($high),
                'dealer_rank' => $finalRank,
            ];
        }
        return $result;
    }

    /**
     * Function to get dealer name
     *
     * @param int $storeId
     * @return string
     */
    public function getDealerName($storeId = null): string
    {
        return $this->storeManager->getStore($storeId)->getWebsite()->getName();
    }

    /**
     * Function to get currency format of metric
     *
     * @param float $metricPerformance
     * @return string
     */
    public function getMetricCurrencyFormat($metricPerformance): string
    {
        return $this->priceHelper->currency(round($metricPerformance, 2));
    }
}
