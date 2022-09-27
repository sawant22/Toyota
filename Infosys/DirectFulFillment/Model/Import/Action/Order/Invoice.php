<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Model\Import\Action\Order;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice as OrderInvoice;
use Xtento\TrackingImport\Model\Import\Action\Order\Invoice as XtentoInvoice;

class Invoice extends XtentoInvoice
{
    /**
     *  Create Invoice
     */
    public function invoice()
    {
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
                        if (
                            isset($itemRecord['direct_fulfillment_status']) &&
                            $itemRecord['direct_fulfillment_status'] == 'SHIPPED'
                        ) {
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

        // Check if order is holded and unhold if should be shipped
        if ($order->canUnhold() && $this->getActionSettingByFieldBoolean('invoice_create', 'enabled')) {
            $order->unhold()->save();
            $this->addDebugMessage(
                __("Order '%1': Order was unholded so it can be invoiced.", $order->getIncrementId())
            );
        }

        $resendInvoiceEmails = $this->getActionSettingByFieldBoolean('invoice_resend_email', 'enabled');
        // Create Invoice
        if ($this->getActionSettingByFieldBoolean('invoice_create', 'enabled')) {
            if ($order->canInvoice()) {
                // Check if invoice increment_id specified in file exists already, if yes, skip import
                if (isset($updateData['invoice_increment_id']) && !empty($updateData['invoice_increment_id'])) {
                    $customInvoiceIncrementId = $updateData['invoice_increment_id'];
                    $invoices = $this->invoiceCollectionFactory->create()
                        ->addAttributeToFilter('increment_id', strval($customInvoiceIncrementId))
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToSort('entity_id', 'desc');
                    if ($invoices->getSize()) {
                        $this->addDebugMessage(
                            __(
                                "Order '%1': Cannot create invoice as invoice increment_id %2 exists already.",
                                $order->getIncrementId(),
                                $customInvoiceIncrementId
                            )
                        );
                        return true;
                    }
                }
                $invoice = false;
                $doInvoiceOrder = true;
                // Partial invoicing support:
                if ($this->getActionSettingByFieldBoolean('invoice_partial_import', 'enabled')) {
                    // Prepare items to invoice for prepareInvoices
                    $qtys = [];
                    foreach ($order->getAllItems() as $orderItem) {
                        // How should the item be identified in the import file?
                        if ($this->getProfileConfiguration()->getProductIdentifier() == 'sku') {
                            $orderItemSku = strtolower(trim($orderItem->getSku()));
                        } elseif ($this->getProfileConfiguration()->getProductIdentifier() == 'order_item_id') {
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
                            $maxQty = $orderItem->getQtyToInvoice();
                            if ($qtyToProcess > $maxQty) {
                                if (($orderItem->getProductType() == Type::TYPE_SIMPLE ||
                                        $orderItem->getProductType() == Type::TYPE_VIRTUAL)
                                    && $orderItem->getParentItem() && $orderItem->getParentItem()->getQtyToInvoice() > 0
                                ) {
                                    // Has a parent item that must be invoiced instead
                                    $orderItemId = $orderItem->getParentItem()->getId();
                                    $maxQty = $orderItem->getParentItem()->getQtyToInvoice();
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
                                $qtys[$orderItemId] = round($qty);
                            } else {
                                $qtys[$orderItemId] = 0;
                            }
                        } else {
                            $qtys[$orderItem->getId()] = 0;
                        }
                    }
                    if (!empty($qtys)) {
                        /** @var $invoice \Magento\Sales\Model\Order\Invoice */
                        $invoice = $order->prepareInvoice($qtys);
                        // Check if proper items have been found in $qtys
                        if (!$invoice->getTotalQty()) {
                            $doInvoiceOrder = false;
                            $this->addDebugMessage(
                                __(
                                    "Order '%1' has NOT been invoiced. Partial invoicing enabled,
                                    however the items specified in the import file couldn't be found in the order.
                                    (Could not find any qtys to invoice)",
                                    $order->getIncrementId()
                                )
                            );
                        }
                    } else {
                        // We're supposed to import partial shipments,
                        // but no SKUs were found at all. Do not touch invoice.
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has NOT been invoiced. Partial invoicing enabled,
                                 however the items specified in the import file couldn't be found in the order.",
                                $order->getIncrementId()
                            )
                        );
                        $doInvoiceOrder = false;
                    }
                } else {
                    /** @var $invoice \Magento\Sales\Model\Order\Invoice */
                    $invoice = $order->prepareInvoice();
                }

                if ($invoice && $doInvoiceOrder) {
                    if (
                        $this->getActionSettingByFieldBoolean(
                            'invoice_capture_payment',
                            'enabled'
                        ) && $invoice->canCapture()
                    ) {
                        // Capture order online
                        $invoice->setRequestedCaptureCase(OrderInvoice::CAPTURE_ONLINE);
                    } else {
                        if ($this->getActionSettingByFieldBoolean('invoice_mark_paid', 'enabled')) {
                            // Set invoice status to Paid
                            $invoice->setRequestedCaptureCase(OrderInvoice::CAPTURE_OFFLINE);
                        }
                    }

                    try {
                        if (
                            isset($updateData['invoice_increment_id']) &&
                            !empty($updateData['invoice_increment_id'])
                        ) {
                            $invoice->setIncrementId($updateData['invoice_increment_id']);
                        }
                        if (isset($updateData['invoice_created_at']) && !empty($updateData['invoice_created_at'])) {
                            $invoice->setCreatedAt($updateData['invoice_created_at']);
                        }
                        $invoice->register();
                    } catch (\Exception $e) {
                        throw new LocalizedException(__($e->getMessage()));
                    }
                    if ($this->getActionSettingByFieldBoolean('invoice_send_email', 'enabled')) {
                        $invoice->setCustomerNoteNotify(true);
                    }
                    $invoice->getOrder()->setIsInProcess(true);

                    // Send email async, schedule to be sent
                    if ($this->getActionSettingByFieldBoolean('send_emails_asynchronously', 'enabled')) {
                        $invoice->setSendEmail(true);
                        $invoice->setEmailSent(null);
                    }

                    $transactionSave = $this->dbTransactionFactory->create()
                        ->addObject($invoice)->addObject($invoice->getOrder());
                    $transactionSave->save();

                    $needsSave = false;
                    if (isset($updateData['invoice_increment_id']) && !empty($updateData['invoice_increment_id'])) {
                        $invoice->setIncrementId($updateData['invoice_increment_id']);
                        $needsSave = true;
                    }
                    if (isset($updateData['invoice_created_at']) && !empty($updateData['invoice_created_at'])) {
                        $invoice->setCreatedAt($updateData['invoice_created_at']);
                        $needsSave = true;
                    }
                    if ($needsSave) {
                        $invoice->save();
                    }

                    $this->setHasUpdatedObject(true);

                    if ($resendInvoiceEmails) {
                        // Will be sent later, below in the code
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been invoiced.",
                                $order->getIncrementId()
                            )
                        );
                    } else if ($this->getActionSettingByFieldBoolean('invoice_send_email', 'enabled')) {
                        if (!$this->getActionSettingByFieldBoolean('send_emails_asynchronously', 'enabled')) {
                            $this->invoiceSender->send($invoice);
                        }
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been invoiced and the customer has been notified.",
                                $order->getIncrementId()
                            )
                        );
                    } else {
                        $this->addDebugMessage(
                            __(
                                "Order '%1' has been invoiced and the customer has NOT been notified.",
                                $order->getIncrementId()
                            )
                        );
                    }

                    unset($invoice);
                }
            } else {
                $this->addDebugMessage(
                    __(
                        "Order '%1' has NOT been invoiced.
                        Order already invoiced or order status not allowing invoicing.",
                        $order->getIncrementId()
                    )
                );
            }
        }

        // Code to attempt to capture existing invoices for an order
        if ($this->getActionSettingByFieldBoolean('invoice_capture_all_invoices', 'enabled')) {
            foreach ($order->getInvoiceCollection() as $invoice) {
                if ($invoice->canCapture()) {
                    try {
                        $invoiceManagement = $this->objectManager->get('\Magento\Sales\Api\InvoiceManagementInterface');
                        $invoiceManagement->setCapture($invoice->getEntityId());
                        $invoice->getOrder()->setIsInProcess(true);
                        $this->objectManager->create(
                            \Magento\Framework\DB\Transaction::class
                        )->addObject(
                            $invoice
                        )->addObject(
                            $invoice->getOrder()
                        )->save();
                    } catch (\Exception $e) {
                        $this->addDebugMessage(
                            __(
                                "Invoice '%1' of order '%2' could not be captured: %3",
                                $invoice->getIncrementId(),
                                $order->getIncrementId(),
                                $e->getMessage()
                            )
                        );
                        continue;
                    }
                    $this->addDebugMessage(
                        __(
                            "Invoice '%1' of order '%2' has been captured. ",
                            $invoice->getIncrementId(),
                            $order->getIncrementId()
                        )
                    );
                }
            }
        }

        if ($resendInvoiceEmails) {
            // Re-send invoice emails
            $invoices = $order->getInvoiceCollection();
            foreach ($invoices as $invoice) {
                $invoice->setCustomerNoteNotify(true);
                if ($this->getActionSettingByFieldBoolean('send_emails_asynchronously', 'enabled')) {
                    $invoice->setSendEmail(true);
                    $invoice->setEmailSent(null);
                } else {
                    $this->invoiceSender->send($invoice);
                }
                $this->addDebugMessage(
                    __(
                        "Order '%1', invoice '%2', invoice email has been sent.",
                        $order->getIncrementId(),
                        $invoice->getIncrementId()
                    )
                );
                $invoice->save();
            }
        }

        return true;
    }
}
