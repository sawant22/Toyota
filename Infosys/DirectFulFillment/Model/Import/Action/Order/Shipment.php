<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Model\Import\Action\Order;

use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\Order;
use Xtento\TrackingImport\Model\Import\Action\Order\Shipment as XtentoShipment;

class Shipment extends XtentoShipment
{
    /**
     * Create Shipment
     *
     * @return void
     */
    public function ship()
    {
        $profileConfig = $this->getProfile()->getConfiguration();
        $applyActionsOnlyToSpecificShipment = false;
        if (
            isset($profileConfig['order_identifier']) &&
            $profileConfig['order_identifier'] == 'shipment_increment_id'
        ) {
            // Do not create a new shipment, only modify specific shipment
            $applyActionsOnlyToSpecificShipment = true;
        }

        /** @var Order $order */
        $order = $this->getOrder();
        $updateData = $this->getUpdateData();

        // Prepare items to process
        $itemsToProcess = [];
        if (isset($updateData['items']) && !empty($updateData['items'])) {
            foreach ($updateData['items'] as $itemRecord) {
                if (isset($itemRecord['sku'])) {
                    $itemRecord['sku'] = strtolower($itemRecord['sku']);
                    if ($this->getActionSettingByFieldBoolean('order_shipment_invoice_action', 'enabled')) {
                        if (isset($itemRecord['direct_fulfillment_status']) &&
                            $itemRecord['direct_fulfillment_status'] == 'SHIPPED'
                        ) {
                            if (
                                isset($itemsToProcess[$itemRecord['sku']])) {
                                $itemsToProcess[$itemRecord['sku']]['qty'] =
                                    $itemsToProcess[$itemRecord['sku']]['qty'] + $itemRecord['qty'];
                            } else {
                                $itemsToProcess[$itemRecord['sku']]['sku'] = $itemRecord['sku'];
                                $itemsToProcess[$itemRecord['sku']]['qty'] = $itemRecord['qty'];
                                $itemsToProcess[$itemRecord['sku']]['shipped_part_number'] =
                                    $itemRecord['shipped_part_number'] ?? '';
                            }
                        }
                    } else {
                        if (isset($itemsToProcess[$itemRecord['sku']])) {
                            $itemsToProcess[$itemRecord['sku']]['qty'] =
                                $itemsToProcess[$itemRecord['sku']]['qty'] + $itemRecord['qty'];
                        } else {
                            $itemsToProcess[$itemRecord['sku']]['sku'] = $itemRecord['sku'];
                            $itemsToProcess[$itemRecord['sku']]['qty'] = $itemRecord['qty'];
                            $itemsToProcess[$itemRecord['sku']]['shipped_part_number'] =
                                $itemRecord['shipped_part_number'] ?? '';
                        }
                    }
                }
            }
        }

        // Prepare tracking numbers to import
        $tracksToImport = [];
        if (isset($updateData['tracks']) && !empty($updateData['tracks'])) {
            foreach ($updateData['tracks'] as $trackRecord) {
                if (empty($trackRecord['tracking_number'])) {
                    continue;
                }
                $tracksToImport[$trackRecord['tracking_number']] = [
                    'tracking_number' => $trackRecord['tracking_number'],
                    'carrier_code' => (isset($trackRecord['carrier_code'])) ? $trackRecord['carrier_code'] : '',
                    'carrier_name' => (isset($trackRecord['carrier_name'])) ? $trackRecord['carrier_name'] : '',
                ];
            }
        }


        #var_dump($updateData, $tracksToImport); die();

        // Check if order is holded and unhold if should be shipped
        if ($order->canUnhold() && $this->getActionSettingByFieldBoolean('shipment_create', 'enabled')) {
            $order->unhold()->save();
            $this->addDebugMessage(
                __("Order '%1': Order was unholded so it can be shipped.", $order->getIncrementId())
            );
        }

        $resendShipmentEmails = $this->getActionSettingByFieldBoolean('shipment_resend_email', 'enabled');
        // Create Shipment
        if ($this->getActionSettingByFieldBoolean('shipment_create', 'enabled')) {
            $doShipOrder = true;
            if (
                $this->getActionSettingByFieldBoolean(
                    'shipment_not_without_trackingnumbers',
                    'enabled'
                ) && empty($tracksToImport)
            ) {
                $doShipOrder = false;
                $this->addDebugMessage(
                    __(
                        "Order '%1': No tracking numbers to import found, not shipping order.",
                        $order->getIncrementId()
                    )
                );
            }
            if ($doShipOrder && !$applyActionsOnlyToSpecificShipment && $order->canShip()) {
                // Check if shipment increment_id specified in file exists already, if yes, skip import
                if (isset($updateData['shipment_increment_id']) && !empty($updateData['shipment_increment_id'])) {
                    $customShipmentIncrementId = $updateData['shipment_increment_id'];
                    $shipments = $this->shipmentCollectionFactory->create()
                        ->addAttributeToFilter('increment_id', strval($customShipmentIncrementId))
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToSort('entity_id', 'desc');
                    if ($shipments->getSize()) {
                        $this->addDebugMessage(
                            __(
                                "Order '%1': Cannot create shipment as shipment increment_id %2 exists already.",
                                $order->getIncrementId(),
                                $customShipmentIncrementId
                            )
                        );
                        return true;
                    }
                }
                // Partial shipment support:
                $shipment = false;
                if ($this->getActionSettingByFieldBoolean('shipment_partial_import', 'enabled')) {
                    // Prepare items to ship for prepareShipment.. but only if there is SKU info in the import file.
                    $data = [];
                    foreach ($order->getAllItems() as $orderItem) {
                        // How should the item be identified in the import file?
                        if ($this->getProfileConfiguration()->getProductIdentifier() == 'sku') {
                            $orderItemSku = strtolower(trim($orderItem->getSku()));
                        } else if ($this->getProfileConfiguration()->getProductIdentifier() == 'order_item_id') {
                            $orderItemSku = $orderItem->getId();
                        } else {
                            if ($this->getProfileConfiguration()->getProductIdentifier() == 'entity_id') {
                                $orderItemSku = trim($orderItem->getProductId());
                            } else {
                                if ($this->getProfileConfiguration()->getProductIdentifier() == 'attribute') {
                                    $product = $this->productFactory->create()->load($orderItem->getProductId());
                                    if ($product->getId()) {
                                        $orderItemSku = strtolower(
                                            trim(
                                                $product->getData(
                                                    $this->getProfileConfiguration()->getProductIdentifierAttributeCode()
                                                )
                                            )
                                        );
                                    } else {
                                        $this->addDebugMessage(
                                            __(
                                                "Order '%1': Product SKU '%2',
                                                product does not exist anymore and cannot be matched for importing.",
                                                $order->getIncrementId(),
                                                $orderItem->getSku()
                                            )
                                        );
                                        continue;
                                    }
                                } else {
                                    $this->addDebugMessage(
                                        __("Order '%1': No method found to match products.", $order->getIncrementId())
                                    );
                                    return true;
                                }
                            }
                        }
                        // Item matched?
                        if (isset($itemsToProcess[$orderItemSku])) {
                            $orderItemId = $orderItem->getId();
                            $qtyToProcess = $itemsToProcess[$orderItemSku]['qty'];
                            $maxQty = $orderItem->getQtyToShip();
                            if ($qtyToProcess > $maxQty) {
                                if (
                                    $orderItem->getProductType() == Type::TYPE_SIMPLE && $orderItem->getParentItem()
                                    && $orderItem->getParentItem()->getQtyToShip() > 0
                                ) {
                                    // Has a parent item that must be shipped instead
                                    $orderItemId = $orderItem->getParentItem()->getId();
                                    $maxQty = $orderItem->getParentItem()->getQtyToShip();
                                    if ($qtyToProcess > $maxQty) {
                                        $qty = round($maxQty);
                                    } else {
                                        $qty = round($qtyToProcess);
                                    }
                                } else {
                                    $qty = round($maxQty);
                                }
                            } else {
                                $qty = round($qtyToProcess);
                            }
                            if ($qty > 0) {
                                $itemsToProcess[$orderItemSku]['qty'] -= $maxQty;
                                $data['items'][$orderItemId] = round($qty);
                            }
                        }
                    }

                    if (!empty($data)) {
                        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                        $shipment = $this->shipmentFactory->create($order, isset($data['items']) ? $data['items'] : []);
                        // Check if proper items have been found in $qtys
                        if (!$shipment->getTotalQty()) {
                            #$shipment = $order->prepareShipment();
                            $doShipOrder = false;
                            $this->addDebugMessage(
                                __(
                                    "Order '%1' has NOT been shipped. Partial shipping enabled,
                                    however the items specified in the import file couldn't be found in the order.
                                    (Could not find any qtys to ship)",
                                    $order->getIncrementId()
                                )
                            );
                        }
                    } else {
                        // We're supposed to import partial shipments, but no SKUs were found at all. Do not touch shipment.
                        $doShipOrder = false;
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has NOT been shipped. Partial shipping enabled,
                                however the items specified in the import file couldn't be found in the order.",
                                $order->getIncrementId()
                            )
                        );
                    }
                } else {
                    $items = [];
                    foreach ($order->getAllItems() as $orderItem) {
                        $items[$orderItem->getId()] = $orderItem->getQtyToShip();
                    }
                    /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                    $shipment = $this->shipmentFactory->create($order, $items);
                }

                /* @var $shipment Order\Shipment */
                if ($shipment && $doShipOrder) {
                    $this->setMsiSource($shipment, isset($updateData['source_code']) ? $updateData['source_code'] : false);
                    if (
                        isset($updateData['order_status_history_comment']) &&
                        !empty($updateData['order_status_history_comment'])
                    ) {
                        $shipment->addComment(
                            $updateData['order_status_history_comment'],
                            false,
                            true
                        );
                        $shipment->setCustomerNote($updateData['order_status_history_comment']);
                    }
                    if (isset($updateData['shipment_increment_id']) && !empty($updateData['shipment_increment_id'])) {
                        $shipment->setIncrementId($updateData['shipment_increment_id']);
                    }
                    if (isset($updateData['shipment_created_at']) && !empty($updateData['shipment_created_at'])) {
                        $shipment->setCreatedAt($updateData['shipment_created_at']);
                    }
                    $shipment->register();
                    if ($this->getActionSettingByFieldBoolean('shipment_send_email', 'enabled')) {
                        $shipment->setCustomerNoteNotify(true);
                    }

                    $shipment->getOrder()->setIsInProcess(true);
                    foreach ($shipment->getAllItems() as $shipmentItem) {
                        if (isset(
                            $itemsToProcess[strtolower($shipmentItem->getSku())]['shipped_part_number']
                        )) {
                            $shipmentItem->setShippedPartNumber(
                                $itemsToProcess[strtolower($shipmentItem->getSku())]['shipped_part_number']
                            );
                        }
                    }

                    //Save Shipment Action
                    $shipment->setShipmentAction('direct_fulfillment');

                    $trackCount = 0;
                    foreach ($tracksToImport as $trackingNumber => $trackData) {
                        $trackCount++;
                        if (
                            !$this->getActionSettingByFieldBoolean(
                                'shipment_multiple_trackingnumbers',
                                'enabled'
                            ) && $trackCount > 1
                        ) {
                            // Do not import more than one tracking number.
                            continue;
                        }
                        $carrierCode = $trackData['carrier_code'];
                        $carrierName = $trackData['carrier_name'];
                        if (empty($carrierCode) && !empty($carrierName)) {
                            $carrierCode = $carrierName;
                        }
                        if (empty($carrierCode) && empty($carrierName)) {
                            $carrierCode = 'custom';
                        }
                        /*if (empty($carrierName) && !empty($carrierCode)) {
                            $carrierName = $carrierCode;
                        }*/
                        if (!empty($trackingNumber)) {
                            $trackingNumber = str_replace("'", "", $trackingNumber);
                            $track = $this->trackFactory->create()->addData(
                                [
                                    'carrier_code' => $this->determineCarrierCode($carrierCode),
                                    'title' => $this->determineCarrierName($carrierName, $carrierCode),
                                    'track_number' => $trackingNumber
                                ]
                            );
                            $shipment->addTrack($track);
                        }
                    }

                    // Send email async, schedule to be sent
                    if ($this->getActionSettingByFieldBoolean('send_emails_asynchronously', 'enabled')) {
                        $shipment->setSendEmail(true);
                        $shipment->setEmailSent(null);
                    }

                    $transactionSave = $this->dbTransactionFactory->create()
                        ->addObject($shipment)->addObject($shipment->getOrder());
                    $transactionSave->save();

                    $needsSave = false;
                    if (isset($updateData['shipment_increment_id']) && !empty($updateData['shipment_increment_id'])) {
                        $shipment->setIncrementId($updateData['shipment_increment_id']);
                        $needsSave = true;
                    }
                    if (isset($updateData['shipment_created_at']) && !empty($updateData['shipment_created_at'])) {
                        $shipment->setCreatedAt($updateData['shipment_created_at']);
                        $needsSave = true;
                    }
                    if ($needsSave) {
                        $shipment->save();
                    }

                    $this->setHasUpdatedObject(true);

                    if ($resendShipmentEmails) {
                        // Will be sent later, below in the code
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been shipped.",
                                $order->getIncrementId()
                            )
                        );
                    } else if ($this->getActionSettingByFieldBoolean('shipment_send_email', 'enabled')) {
                        // Fix for shipment email containing other tracking numbers from same order
                        $reflection = new \ReflectionClass($shipment);
                        if ($reflection->hasProperty('tracksCollection')) {
                            $reflectionProperty = $reflection->getProperty('tracksCollection');
                            $reflectionProperty->setAccessible(true);
                            $reflectionProperty->setValue($shipment, null);
                        }
                        if (!$this->getActionSettingByFieldBoolean('send_emails_asynchronously', 'enabled')) {
                            // Send shipment email
                            $this->shipmentSender->send($shipment);
                        }
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been shipped and the customer has been notified.",
                                $order->getIncrementId()
                            )
                        );
                    } else {
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been shipped and the customer has NOT been notified.",
                                $order->getIncrementId()
                            )
                        );
                    }

                    $this->setHasUpdatedObject(true);

                    unset($shipment);
                }
            } else {
                $this->addDebugMessage(
                    __(
                        "Order '%1' has NOT been shipped. Already shipped or order status not allowing shipping.",
                        $order->getIncrementId()
                    )
                );
            }
        }

        // All items of that order have been shipped but there are more tracking numbers?
        //Try to load the last shipment and still add the tracking number.
        if (($applyActionsOnlyToSpecificShipment || !$order->canShip()) && !empty($tracksToImport)) {
            if ($this->getActionSettingByFieldBoolean('shipment_multiple_trackingnumbers', 'enabled')) {
                // Add a second/third/whatever tracking number to the shipment - if possible.
                $shipments = $this->shipmentCollectionFactory->create()
                    ->setOrderFilter($order)
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToSort('entity_id', 'desc');
                // Customization: Add tracking# to shipment# specified as order_identifier,
                //i.e. when loading orders via shipment# in profile.
                if ($applyActionsOnlyToSpecificShipment) {
                    // Only add to this shipment ID
                    $shipments->addAttributeToFilter('increment_id', $updateData['order_identifier']);
                }
                // End Customization
                $shipments->setPage(1, 1);
                $lastShipment = $shipments->getFirstItem();
                if ($lastShipment->getId()) {
                    /** @var \Magento\Sales\Model\Order\Shipment $lastShipment */
                    $lastShipment = $this->shipmentRepository->get($lastShipment->getId());
                    $this->setMsiSource($lastShipment, isset($updateData['source_code']) ?
                        $updateData['source_code'] : false);

                    $newTrackAdded = false;
                    foreach ($tracksToImport as $trackingNumber => $trackData) {
                        $carrierCode = $trackData['carrier_code'];
                        $carrierName = $trackData['carrier_name'];
                        if (empty($carrierCode) && !empty($carrierName)) {
                            $carrierCode = $carrierName;
                        }
                        if (empty($carrierCode) && empty($carrierName)) {
                            $carrierCode = 'custom';
                        }
                        /*if (empty($carrierName) && !empty($carrierCode)) {
                            $carrierName = $carrierCode;
                        }*/
                        $trackAlreadyAdded = false;
                        foreach ($lastShipment->getAllTracks() as $trackInfo) {
                            if ($trackInfo->getTrackNumber() == $trackingNumber) {
                                $trackAlreadyAdded = true;
                                break;
                            }
                        }
                        if (!$trackAlreadyAdded) {
                            if (!empty($trackingNumber)) {
                                // Determine carrier and add tracking number
                                $trackingNumber = str_replace("'", "", $trackingNumber);
                                $track = $this->trackFactory->create()->addData(
                                    [
                                        'carrier_code' => $this->determineCarrierCode($carrierCode),
                                        'title' => $this->determineCarrierName($carrierName, $carrierCode),
                                        'track_number' => $trackingNumber
                                    ]
                                );
                                $lastShipment->addTrack($track)->save();
                                $newTrackAdded = true;
                            }
                        }
                    }
                    if ($newTrackAdded) {
                        if ($resendShipmentEmails) {
                            // Will be sent later, below in the code
                            $this->addDebugMessage(
                                __(
                                    "Order '%1' has been shipped.",
                                    $order->getIncrementId()
                                )
                            );
                        } else if ($this->getActionSettingByFieldBoolean('shipment_send_email', 'enabled')) {
                            // Fix for shipment email containing other tracking numbers from same order
                            $reflection = new \ReflectionClass($lastShipment);
                            if ($reflection->hasProperty('tracksCollection')) {
                                $reflectionProperty = $reflection->getProperty('tracksCollection');
                                $reflectionProperty->setAccessible(true);
                                $reflectionProperty->setValue($lastShipment, null);
                            }
                            // Re-send shipment email when another tracking number was added.
                            $this->shipmentSender->send($lastShipment);
                            $this->addDebugMessage(
                                __(
                                    "Order '%1': Another tracking number was added for the last
                                    hipment (Multi-Tracking) and the customer has been notified.",
                                    $order->getIncrementId()
                                )
                            );
                        } else {
                            $this->addDebugMessage(
                                __(
                                    "Order '%1': Another tracking number was added for the last
                                    shipment (Multi-Tracking) and the customer has NOT been notified.",
                                    $order->getIncrementId()
                                )
                            );
                        }
                        $this->setHasUpdatedObject(true);
                    }
                }
            }
        }

        if ($resendShipmentEmails) {
            // Re-send shipment emails
            $shipments = $order->getShipmentsCollection();
            foreach ($shipments as $shipment) {
                $shipment->setCustomerNoteNotify(true);
                $this->shipmentSender->send($shipment);
                $this->addDebugMessage(
                    __(
                        "Order '%1', shipment '%2', shipment email has been sent.",
                        $order->getIncrementId(),
                        $shipment->getIncrementId()
                    )
                );
                $shipment->save();
            }
        }

        return true;
    }
}
