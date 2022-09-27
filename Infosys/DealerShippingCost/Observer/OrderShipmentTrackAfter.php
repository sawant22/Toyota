<?php

/**
 * @package     Infosys/DealerShippingCost
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DealerShippingCost\Observer;

use Infosys\DealerShippingCost\Model\ShipstationShippingCost;
use Infosys\DealerShippingCost\Model\ManualShippingCost;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Infosys\DealerShippingCost\Logger\ShippingCostLogger;
use Infosys\DealerShippingCost\Helper\Data;
use Infosys\DirectFulFillment\Model\FreightRecoveryFactory;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class to handle shipping cost's from different sources
 */
class OrderShipmentTrackAfter implements ObserverInterface
{
    protected ShipstationShippingCost $shipstationShippingCostModel;

    protected ShippingCostLogger $logger;

    protected Data $helper;

    protected FreightRecoveryFactory $freightRecoveryFactory;

    protected Http $request;

    protected ManualShippingCost $manualShippingCostModel;

    protected StoreManagerInterface $storeManager;

    /**
     * Constructor function
     *
     * @param ShipstationShippingCost $shipstationShippingCostModel
     * @param ShippingCostLogger $logger
     * @param Data $helper
     * @param FreightRecoveryFactory $freightRecoveryFactory
     * @param Http $request
     * @param ManualShippingCost $manualShippingCostModel
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ShipstationShippingCost $shipstationShippingCostModel,
        ShippingCostLogger $logger,
        Data $helper,
        FreightRecoveryFactory $freightRecoveryFactory,
        Http $request,
        ManualShippingCost $manualShippingCostModel,
        StoreManagerInterface $storeManager
    ) {
        $this->shipstationShippingCostModel = $shipstationShippingCostModel;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->freightRecoveryFactory = $freightRecoveryFactory;
        $this->request = $request;
        $this->manualShippingCostModel = $manualShippingCostModel;
        $this->storeManager = $storeManager;
    }

    /**
     * Observer to handle shipping cost's from different sources
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $shipmentTrack = $observer->getEvent()->getTrack();
            $shipment = $shipmentTrack->getShipment();
            $order = $shipment->getOrder();
            $storeId = $order->getStore()->getId();
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
            $this->logger->info("Current Shipment " . $shipment->getShipmentAction());

            if ($shipment->getShipmentAction() == 'manual_shipment') {
                $dealer_shipping_cost = 0;
                $tracking = $this->request->getParam('tracking');
                if (!empty($tracking)) {
                    foreach ($tracking as $track) {
                        if (!empty($track['shipping_cost'])) {
                            $dealer_shipping_cost += (float)$track['shipping_cost'];
                        }
                    }
                }
                $this->manualShippingCostModel->execute($dealer_shipping_cost, $shipment);
            } elseif ($shipment->getShipmentAction() == 'direct_fulfillment') {
                $freightRecovery = $this->freightRecoveryFactory->create();
                $freightRecovery->setOrderId($order->getId());
                $freightRecovery->setShipmentId($shipment->getId());
                $freightRecovery->setAction('direct_fulfillment');
                $freightRecovery->save();
            } else {
                if ($this->helper->isShipstationEnabled($websiteId)) {
                    $this->shipstationShippingCostModel->execute($shipment, $storeId);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("Error in dealer shipping cost handling observer" . $e);
        }
    }
}
