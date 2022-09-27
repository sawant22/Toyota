<?php

/**
 * @package     Infosys/SalesReport
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2022. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class to install the stored procedure for sales report calculations
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Function to install the stored procedure for sales report calculations
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();
        $dropProcedure = "DROP PROCEDURE IF EXISTS calculateSalesStatistics";
        $setup->getConnection()->exec($dropProcedure);

        $createProcedureSql = "CREATE PROCEDURE calculateSalesStatistics (
                     IN storeId INT,
                     IN cDate DATE
        )
        BEGIN
        SELECT COUNT(sales_order.entity_id), 
             ROUND(SUM((sales_order.base_subtotal - ABS(IFNULL(sales_order.base_subtotal_refunded,0))) 
                     - 
                     (ABS(IFNULL(sales_order.base_discount_amount,0)) - ABS(IFNULL(sales_order.base_discount_refunded,0)))
                 ),2),
             ROUND(SUM(IFNULL(sales_order.base_shipping_amount,0) - 
                 (IFNULL(sales_order.base_shipping_refunded,0) + (IFNULL(sales_order.base_shipping_discount_amount,0)) )
             ),2),
             ROUND(SUM(ABS(IFNULL(sales_order.base_discount_amount,0)) - ABS(IFNULL(sales_order.base_discount_refunded,0))),2)
             INTO @orderQuantity, @productSales, @shippingSales, @totalDiscount FROM sales_order WHERE sales_order.status!='canceled' AND sales_order.status!='closed' AND DATE(sales_order.created_at) = cDate AND sales_order.store_id=storeId;
 
        set @totalNetSales = (SELECT ROUND(SUM(
             sales_order.base_subtotal - ABS(IFNULL(sales_order.base_subtotal_refunded,0))
             - (
                 ABS(IFNULL(sales_order.base_discount_amount,0)) - ABS(IFNULL(sales_order.base_discount_refunded,0))
                 )
             - (
                 IFNULL(sales_order.base_shipping_amount,0) - IFNULL(sales_order.base_shipping_refunded,0)
             ) 
             ),2)
             from sales_order where sales_order.status!='canceled' AND sales_order.status!='closed' AND DATE(sales_order.created_at) = cDate AND sales_order.store_id=storeId);
             
        set @productSalesMSRP = (SELECT ROUND(SUM(
             (sales_order_item.base_original_price*(sales_order_item.qty_ordered - sales_order_item.qty_returned + sales_order_item.qty_canceled))
                 - 
                 (
                 IFNULL(sales_order_item.base_discount_amount,0)
                 -
                 IFNULL(sales_order_item.base_discount_refunded,0)
                 )
             ),2)
             FROM sales_order_item
             INNER JOIN sales_order ON sales_order.entity_id = sales_order_item.order_id
             WHERE sales_order.status!='canceled' AND sales_order.status!='closed' AND DATE(sales_order.created_at) = cDate AND sales_order.store_id=storeId);    
             
        set @productCOGS = (SELECT ROUND(SUM(
             IFNULL(sales_order_item.base_cost,0)*(sales_order_item.qty_ordered - (sales_order_item.qty_returned + sales_order_item.qty_canceled))
             ),2)
             FROM sales_order_item
             INNER JOIN sales_order ON sales_order.entity_id = sales_order_item.order_id
             WHERE sales_order.status!='canceled' AND sales_order.status!='closed' AND DATE(sales_order.created_at) = cDate AND sales_order.store_id=storeId);
 
        set @shippongCOGS = (SELECT IFNULL(ROUND(SUM(IFNULL(df_sales_order_freight_recovery.freight_recovery,0) + IFNULL(sales_shipment_item.dealer_shipping_amount,0)),2),0) FROM sales_order
             INNER JOIN sales_order_item ON sales_order.entity_id = sales_order_item.order_id
             INNER JOIN df_sales_order_freight_recovery ON df_sales_order_freight_recovery.order_id = sales_order_item.order_id
             INNER JOIN sales_shipment_item ON sales_order.entity_id = sales_shipment_item.order_item_id 
             WHERE sales_order.status!='canceled' AND sales_order.status!='closed' AND DATE(sales_order.created_at) = cDate AND sales_order.store_id=storeId);
         
        set @totalItemQuantity = (
             SELECT SUM(
                 (sales_order_item.qty_ordered) - (sales_order_item.qty_returned + sales_order_item.qty_canceled)   
             )
             from sales_order_item
             INNER JOIN sales_order ON sales_order.entity_id = sales_order_item.order_id
             WHERE sales_order.status!='canceled' AND sales_order.status!='closed' AND DATE(sales_order.created_at) = cDate AND sales_order.store_id=storeId);  
 
        set @shippingGrossProfit = (@shippingSales - @shippongCOGS);    
                             
        set @totalGrossSales =(@productSalesMSRP + @shippingSales);      
             
        set @productGrossProfit =(@productSales - @productCOGS);  
 
        set @totalGrossProfit =(@productGrossProfit + @shippingGrossProfit);  
 
        set @grossProfitPerOrder =ROUND((@totalGrossProfit / @orderQuantity),2);  
 
        set @productGrossProfitPercentage = ROUND((((@productSales - @productCOGS)/@productSales)*100),2);
 
        set @totalGrossProfitPercentage = ROUND((((@totalGrossSales - @productCOGS)/@productSales)*100),2);
 
        set @partsPercentage = (
             SELECT(
             (
                 SELECT ROUND(SUM((sales_order_item.qty_ordered) - (sales_order_item.qty_returned + sales_order_item.qty_canceled)),2)
                 from sales_order
                 INNER JOIN sales_order_item ON sales_order.entity_id = sales_order_item.order_id
                 INNER JOIN catalog_product_entity ON catalog_product_entity.sku = sales_order_item.sku
                 INNER JOIN eav_attribute_set ON eav_attribute_set.attribute_set_id = catalog_product_entity.attribute_set_id
                 WHERE sales_order.status!='canceled' AND sales_order.status!='closed' AND DATE(sales_order.created_at) = cDate AND sales_order.store_id=storeId AND eav_attribute_set.attribute_set_name = 'Service Part'
             ) / 
             (@totalItemQuantity) * 100));
             
        set @accessoryPercentage = (
             SELECT(
             (
                 SELECT ROUND(SUM((sales_order_item.qty_ordered) - (sales_order_item.qty_returned + sales_order_item.qty_canceled)),2)
                 from sales_order
                 INNER JOIN sales_order_item ON sales_order.entity_id = sales_order_item.order_id
                 INNER JOIN catalog_product_entity ON catalog_product_entity.sku = sales_order_item.sku
                 INNER JOIN eav_attribute_set ON eav_attribute_set.attribute_set_id = catalog_product_entity.attribute_set_id
                 WHERE sales_order.status!='canceled' AND sales_order.status!='closed' AND DATE(sales_order.created_at) = cDate AND sales_order.store_id=storeId AND eav_attribute_set.attribute_set_name = 'Accessory'
             ) / 
             (@totalItemQuantity) * 100));
 
        set @timeToShip = (
             SElECT AVG(ABS(timediff(sales_order.created_at,sales_shipment.created_at)/3600)) from sales_order INNER JOIN sales_shipment ON sales_order.entity_id=sales_shipment.order_id WHERE sales_order.status!='canceled' AND sales_order.status!='closed' AND DATE(sales_order.created_at) = cDate AND sales_order.store_id=storeId);    
 
            INSERT INTO toyota_dealer_sales_statistics (report_date, orders_qty, product_sales, percent_parts, percent_accessories, shipping_sales, total_net_sales, total_gross_sales,product_gross_profit, shipping_gross_profit, total_gross_profit, gross_profit_per_order, product_gross_profit_percent, total_gross_profit_percent, total_discount, time_to_ship, store_id) VALUES (cDate, @orderQuantity, @productSales, @partsPercentage, @accessoryPercentage, @shippingSales, @totalNetSales, @totalGrossSales, @productGrossProfit, @shippingGrossProfit, @totalGrossProfit, @grossProfitPerOrder, @productGrossProfitPercentage, @totalGrossProfitPercentage, @totalDiscount, @timeToShip, storeId) ON DUPLICATE KEY UPDATE report_date = cDate, orders_qty = @orderQuantity, product_sales = @productSales,percent_parts = @partsPercentage, percent_accessories = @accessoryPercentage, shipping_sales = @shippingSales, total_net_sales = @totalNetSales, total_gross_sales = @totalGrossSales, product_gross_profit = @productGrossProfit, shipping_gross_profit = @shippingGrossProfit, total_gross_profit = @totalGrossProfit, gross_profit_per_order = @grossProfitPerOrder, product_gross_profit_percent = @productGrossProfitPercentage, total_gross_profit_percent = @totalGrossProfitPercentage , total_discount = @totalDiscount, time_to_ship = @timeToShip, store_id = storeId;
        END";
        $setup->getConnection()->exec($createProcedureSql);
        $setup->endSetup();
    }
}
