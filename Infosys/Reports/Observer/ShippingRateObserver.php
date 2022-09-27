<?php
/**
 * @package     Infosys/Reports
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Reports\Observer;

use \Magento\Framework\Event\ObserverInterface;

class ShippingRateObserver implements ObserverInterface
{
    /**
     * @var shipperDataHelper
     */
    public $shipperDataHelper;

    /**
     * @var shippingDetail
     */
    public $shippingDetail;

    /**
     * Contruct function
     *
     * @param Data $shipperDataHelper
     * @param Detail $shippingDetail
     */
    public function __construct(  
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Model\Order\Detail $shippingDetail
    )
    {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->shippingDetail = $shippingDetail;
    }
    /**
     * Execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = $observer->getEvent()->getOrder()->getId();
        $order = $observer->getEvent()->getOrder();
        $shipPrice = "";
        $ordershippingPrice = '';
        if($orderId) {
            $orderDetail = $this->shippingDetail->loadByOrder($orderId);
            foreach ($orderDetail as $order) {
                $ordershippingPrice = $order->getCarrierGroupDetail();
                if($ordershippingPrice!= "") {
                    $price = $this->shipperDataHelper->decodeShippingDetails($ordershippingPrice);
                    $shipPrice = $price['0']['price'];
                    $order->setData('freeship_coupon_discount', $shipPrice);
                    $order->save();
                }
            }
        }
    }
}
