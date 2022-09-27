<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Helper class for direct fulfillment
 */
class DirectFulFillment extends AbstractHelper
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor function
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Method to check the order conditions
     *
     * @param object $order
     * @return void
     */
    public function checkOrder($order)
    {
        $storeId = $order->getStore()->getId();
        $df_status = $this->scopeConfig->getValue(
            'df_config/df_config_group/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $us_state_filter = $this->scopeConfig->getValue(
            'df_config/df_config_group/us_state_filter',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $us_state_filter_array = explode(',', $us_state_filter);

        $region = '';
        if (!empty($order->getShippingAddress())) {
            $region = $order->getShippingAddress()->getData('region');
        }

        $flag = 1;
        if ($df_status != 1) {
            $flag = 0;
        } else {
            if (!in_array($region, $us_state_filter_array)) {
                $flag = 0;
            } else {
                if ($order->getShippingMethod() == 'dealerstore_pickup') {
                    $flag = 0;
                }
            }
        }
        $allItems = $order->getAllVisibleItems();
        $itemStatus = 0;
        foreach ($allItems as $_item) {
            if ($this->checkOrderItem($_item)) {
                $itemStatus = 1;
            }
        }
        if (!$itemStatus) {
            $flag = 0;
        }
        $order->setData('direct_fulfillment_status', $flag);
        $order->save();
    }

    /**
     * Method to check order item
     *
     * @param object $_item
     * @return int
     */
    public function checkOrderItem($_item)
    {
        $sku = $_item->getData()['sku'];
        $qty_ordered = $_item->getData()['qty_ordered'];
        return $this->checkItem($sku, $qty_ordered, $_item);
    }

    /**
     * Method to check item conditions
     *
     * @param string $productSku
     * @param int $qty
     * @param object $_item
     * @return int
     */
    public function checkItem($productSku, $qty, $_item)
    {

        $flag = 1;
        $_product = $this->productRepository->get($productSku);
        $qup = $_product->getData('qup');
        $spaoFillable = $_product->getData('spao_fillable');
        if ($spaoFillable == 'Y') {
            if (!empty($qup) && is_numeric($qup)) {
                if ($qty < $qup) {
                    $flag = 0;
                } elseif (($qty % $qup) > 0) {
                    $flag = 0;
                }
            }
        } else {
            $flag = 0;
        }
        $_item->setData('direct_fulfillment_eligibility', $flag);
        $_item->save();
        return $flag;
    }
    /**
     * Get Export Profile Id
     *
     * @param int $storeId
     * @return void
     */
    public function getExportProfileId($storeId)
    {
        return  $this->scopeConfig->getValue(
            'df_config/df_config_group/df_export_profile',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
