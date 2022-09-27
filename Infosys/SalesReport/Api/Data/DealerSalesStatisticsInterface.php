<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Toyota dealer sales statistics table interface
 */
interface DealerSalesStatisticsInterface extends ExtensibleDataInterface
{

    const TOYOTA_DEALER_SALES_STATISTICS_TABLE = 'toyota_dealer_sales_statistics';

    const ID = 'entity_id';

    const DATE = 'report_date';

    const STORE_ID = 'store_id';

    const ORDERS_QTY = 'orders_qty';

    const PRODUCT_SALES = 'product_sales';

    const PERCENT_PARTS = 'percent_parts';

    const PERCENT_ACCESSORIES = 'percent_accessories';

    const SHIPPING_SALES = 'shipping_sales';

    const TOTAL_NET_SALES = 'total_net_sales';

    const TOTAL_GROSS_SALES = 'total_gross_sales';

    const PRODUCT_GROSS_PROFIT = 'product_gross_profit';

    const SHIPPING_GROSS_PROFIT = 'shipping_gross_profit';

    const TOTAL_GROSS_PROFIT = 'total_gross_profit';

    const GROSS_PROFIT_PER_ORDER = 'gross_profit_per_order';

    const PRODUCT_GROSS_PROFIT_PERCENT = 'product_gross_profit_percent';

    const TOTAL_GROSS_PROFIT_PERCENT = 'total_gross_profit_percent';

    const TOTAL_DISCOUNT = 'total_discount';

    const TIME_TO_RECEIVE = 'time_to_receive';

    const TIME_TO_SHIP = 'time_to_ship';

    /**
     * Entity id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set entity id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Dealer sales statistics date
     *
     * @return string
     */
    public function getReportDate();

    /**
     * Set sales statistics date
     *
     * @param string $date
     * @return $this
     */
    public function setReportDate($date);

    /**
     * Store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set Store id
     *
     * @param id $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Orders qty
     *
     * @return int|null
     */
    public function getOrdersQty();

    /**
     * Set orders qty
     *
     * @param int $ordersQty
     * @return $this
     */
    public function setOrdersQty($ordersQty);

    /**
     * Product sales
     *
     * @return string|null
     */
    public function getProductSales();
    /**
     * Set product sales
     *
     * @param string $productSales
     * @return $this
     */
    public function setProductSales($productSales);

    /**
     * Percent parts
     *
     * @return string|null
     */
    public function getPercentParts();

    /**
     * Set percent parts
     *
     * @param string $percentParts
     * @return $this
     */
    public function setPercentParts($percentParts);

    /**
     * Percent accessories
     *
     * @return string|null
     */
    public function getPercentAccessories();

    /**
     * Set percent accessories
     *
     * @param string $percentAccessories
     * @return $this
     */
    public function setPercentAccessories($percentAccessories);

    /**
     * Shipping sales
     *
     * @return string|null
     */
    public function getShippingSales();

    /**
     * Set shipping sales
     *
     * @param string $shippingSales
     * @return $this
     */
    public function setShippingSales($shippingSales);

    /**
     * Total net sales
     *
     * @return string|null
     */
    public function getTotalNetSales();

    /**
     * Set total net sales
     *
     * @param string $totalNetSales
     * @return $this
     */
    public function setTotalNetSales($totalNetSales);

    /**
     * Total gross sales
     *
     * @return string|null
     */
    public function getTotalGrossSales();

    /**
     * Set total gross sales
     *
     * @param string $totalGrossSales
     * @return $this
     */
    public function setTotalGrossSales($totalGrossSales);

    /**
     * Product gross profit
     *
     * @return string|null
     */
    public function getProductGrossProfit();

    /**
     * Set product gross profit
     *
     * @param string $productGrossProfit
     * @return $this
     */
    public function setProductGrossProfit($productGrossProfit);

    /**
     * Shipping gross profit
     *
     * @return string|null
     */
    public function getShippingGrossProfit();

    /**
     * Set shipping gross profit
     *
     * @param string $shippingGrossProfit
     * @return $this
     */
    public function setShippingGrossProfit($shippingGrossProfit);

    /**
     * Total Gross Profit
     *
     * @return string|null
     */
    public function getTotalGrossProfit();

    /**
     * Set total gross profit
     *
     * @param string $totalGrossProfit
     * @return $this
     */
    public function setTotalGrossProfit($totalGrossProfit);

    /**
     * Gross profit per order
     *
     * @return string|null
     */
    public function getGrossProfitPerOrder();

    /**
     * Set gross profit per order
     *
     * @param string $grossProfitPerOrder
     * @return $this
     */
    public function setGrossProfitPerOrder($grossProfitPerOrder);

    /**
     * Product gross profit percent
     *
     * @return string|null
     */
    public function getProductGrossProfitPercent();

    /**
     * Set product gross profit percent
     *
     * @param string $productGrossProfitPercent
     * @return $this
     */
    public function setProductGrossProfitPercent($productGrossProfitPercent);

    /**
     * Total gross profit percent
     *
     * @return string|null
     */
    public function getTotalGrossProfitPercent();

    /**
     * Set total gross profit percent
     *
     * @param string $totalGrossProfitPercent
     * @return $this
     */
    public function setTotalGrossProfitPercent($totalGrossProfitPercent);

    /**
     * Total discount
     *
     * @return string|null
     */
    public function getTotalDiscount();

    /**
     * Set total discount
     *
     * @param string $totalDiscount
     * @return $this
     */
    public function setTotalDiscount($totalDiscount);

    /**
     * Time to receive
     *
     * @return int|null
     */
    public function getTimeToReceive();

    /**
     * Set time to receive
     *
     * @param int $timeToReceive
     * @return $this
     */
    public function setTimeToReceive($timeToReceive);

    /**
     * Time to ship
     *
     * @return int|null
     */
    public function getTimeToShip();

    /**
     * Set time to ship
     *
     * @param int $timeToShip
     * @return $this
     */
    public function setTimeToShip($timeToShip);
}
