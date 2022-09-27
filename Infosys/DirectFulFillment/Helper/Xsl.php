<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Helper;

use Magento\Framework\App\ObjectManager;

class Xsl extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get Dealer Code for Order
     *
     * @param int $storeId
     * @return void
     */
    public static function getDealerCode($storeId)
    {
        // Needs to use the object manager as this is a static function (which is required for XSL)
        $storeManager = ObjectManager::getInstance()->create('\Magento\Store\Model\StoreManagerInterface');
        $websiteId = (int)$storeManager->getStore($storeId)->getWebsiteId();
        return $storeManager->getWebsite($websiteId)->getDealerCode();
    }
    /**
     * Get Website Url for Order
     *
     * @param int $storeId
     * @return void
     */
    public static function getWebsiteUrl($storeId)
    {
        // Needs to use the object manager as this is a static function (which is required for XSL)
        $storeManager = ObjectManager::getInstance()->create('\Magento\Store\Model\StoreManagerInterface');
        return $storeManager->getStore($storeId)->getBaseUrl();
    }
    /**
     * Get Time format in UTC
     *
     * @param  $time
     * @return void
     */
    public static function getTimeFormat($time)
    {
        return  gmdate("Y-m-d\TH:i:s\Z", $time);
    }
    /**
     * Get Uuid
     *
     * @return void
     */
    public static function getUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            random_int(0, 0xffff),
            random_int(0, 0xffff),

            // 16 bits for "time_mid"
            random_int(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            random_int(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            random_int(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
    /**
     * Get Dealer Shipping Amount
     * @param string $orderId
     *
     * @return string
     */
    public static function getTotalDealerShippingAmount($orderId)
    {
        $freightRecovery = ObjectManager::getInstance()->create('\Infosys\DirectFulFillment\Model\FreightRecoveryFactory')->create();
        $totalShippingAmount = 0;
        $collection = $freightRecovery->getCollection()->addFieldToSelect('*')
                    ->addFieldToFilter('order_id', ['eq' => $orderId])
                    ->addFieldToFilter('action', ['in' => ['manual','shipstation']]);
        if($collection->count()){
            foreach($collection as $shipment){
                $totalShippingAmount += $shipment->getFreightRecovery();
            }
        }
        return $totalShippingAmount;
    }

    /**
     * Check order send to DF
     * @param string $orderId
     *
     * @return string
     */
    public static function isDirectFulfillmentOrder($orderId)
    {
        $order = ObjectManager::getInstance()->create('\Magento\Sales\Model\Order')->load($orderId);
        foreach ($order->getAllItems() as $orderItems) {
            if ($orderItems->getDealerDirectFulfillmentStatus()) {
                return 1;
            }
        }
        return  0;
    }

    /**
     * get Order Notes
     * @param string $orderItemId
     * @param string $orderId
     *
     * @return string
     */
    public static function getNotes($orderItemId, $orderId)
    {
        $orderItem = ObjectManager::getInstance()->create('\Magento\Sales\Model\Order\Item')->load($orderItemId);
        $order = ObjectManager::getInstance()->create('\Magento\Sales\Model\Order')->load($orderId);
        $currentTime = strtotime("now");
        $acceptedTime = strtotime((string)$order->getDirectFulfillmentOrderAcceptedAt());
        $directFulfillmentStatus =  strtolower($orderItem->getDirectFulfillmentStatus());
        $orderItemStatus = strtolower($orderItem->getStatus());
        $diff = abs($currentTime - $acceptedTime);
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24)
        / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 -
        $months*30*60*60*24)/ (60*60*24));
        $hours = floor(($diff - $years * 365*60*60*24
        - $months*30*60*60*24 - $days*60*60*24)
                                    / (60*60));
        if (($hours > 4) && ($directFulfillmentStatus =="sent to direct fulfillment")) {
            return "No acknowledgement by DDOA within 4 hours";
        }
        if (($days > 0) && ($directFulfillmentStatus =="accepted")) {
            return "No updates since Accepted by DDOA";
        }
        if ($directFulfillmentStatus == 'back order') {
            return "Backorder";
        }
        if ($orderItemStatus == 'shipped') {
            $tracksCollection = $order->getTracksCollection();
            foreach ($tracksCollection->getItems() as $item) {
                $trackingInfo = $item->getTrackNumber(); // Tracking number
            }
            if (empty($trackingInfo)) {
                return "No tracking number provided by APS";
            }
        }
    }

    /**
     * get Eta date for back order
     * @param string $orderItemId
     *
     * @return string
     */
    public static function getEtaDate($orderItemId)
    {
        $orderItem = ObjectManager::getInstance()->create('\Magento\Sales\Model\Order\Item')->load($orderItemId);
        $directFulfillmentStatus =  strtolower($orderItem->getDirectFulfillmentStatus());
        $directFulfillmentResponse =  strtolower($orderItem->getDirectFulfillmentResponse());
        if ($directFulfillmentStatus == 'back order' && strpos($directFulfillmentResponse, 'eta date') !== false) {
            $etaDate = explode(":", $directFulfillmentResponse);
            if (isset($etaDate['1'])) {
                return $etaDate['1'];
            }
        }
    }

    /**
     * get direct order fulfillment accepted date
     * @param string $date
     *
     * @return string
     */
    public static function getDoaAcceptedDate($date)
    {
        $ddoaAcceptedDate = '';
        if($date != NULL){
            $ddoaAcceptedDate = date('m/d/Y', strtotime($date));
        }
        return $ddoaAcceptedDate;
    }
}
