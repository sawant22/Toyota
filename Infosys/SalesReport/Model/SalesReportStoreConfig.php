<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class to get store configuration values
 */
class SalesReportStoreConfig
{
    const XML_LOG_ENABLED = 'sales_report_config/logging_errors/active';

    const XML_MAX_RECORDS_COUNT = 'sales_report_config/sales_report_max_records/maximum_records_count';

    protected ScopeConfigInterface $scopeConfig;

    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is Log Enabled function
     *
     * @return bool
     */
    public function isLogEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_LOG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Method to get maximum records count to calculate sales report
     *
     * @return string
     */
    public function getMaxRecordsToCalculateReports(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_MAX_RECORDS_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Method to get dealer rank metrics
     *
     * @return array
     */
    public function getDealerRankMetrics(): array
    {
        return [
            'orders_qty' => ['Orders Quantity', 0],
            'product_sales' => ['Product Sales', 1],
            'percent_parts' => ['Parts Percentage', 0],
            'percent_accessories' => ['Accessories Percentage', 0],
            'shipping_sales' => ['Shipping Sales', 1],
            'total_net_sales' => ['Total Net Sales', 1],
            'total_gross_sales' => ['Total Gross Sales', 1],
            'product_gross_profit' => ['Product Gross Profit', 1],
            'shipping_gross_profit' => ['Shipping Gross Profit', 1],
            'total_gross_profit' => ['Total Gross Profit', 1],
            'gross_profit_per_order' => ['Gross Profit Per Order', 1],
            'product_gross_profit_percent' => ['Product Gross Profit Percentage', 0],
            'total_gross_profit_percent' => ['Total Gross Profit Percentage', 0],
            'total_discount' => ['Total Discount', 1],
            'time_to_ship' => ['Time to Ship', 0]
        ];
    }
}
