<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Infosys\DirectFulFillment\Helper\Xsl;
use Infosys\DirectFulfillment\Logger\DDOALogger;

/**
 * Class to update exported objects count from xtento
 */
class OrderExportAfter implements ObserverInterface
{
    protected Xsl $xslHelper;

    protected DDOALogger $ddoaLogger;

    /**
     * Constructor function
     *
     * @param Xsl $xslHelper
     * @param DDOALogger $ddoaLogger
     */
    public function __construct(
        Xsl $xslHelper,
        DDOALogger $ddoaLogger
    ) {
        $this->xslHelper = $xslHelper;
        $this->ddoaLogger = $ddoaLogger;
    }

    /**
     * Method to update exported objects count from xtento
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $dfOrders = [];
        $objects = $observer->getData('objects');
        $profile = $observer->getData('profile');
        $log = $observer->getData('log');
        try {
            if ($profile->getEntity() == 'shipment') {
                if (!empty($objects) && !empty($log)) {
                    foreach ($objects as $object) {
                        $isOrderDf = $this->xslHelper->isDirectFulfillmentOrder($object['order_id']);
                        if ($isOrderDf) {
                            $dfOrders[] = $object['order_id'];
                        }
                    }
                    $log->setRecordsExported(count($dfOrders));
                    $log->setResultMessage(
                        $log->getResultMessages() ? $log->getResultMessages() : __(
                            'Export of %1 %2s finished in %3 seconds.',
                            count($dfOrders),
                            $profile->getEntity(),
                            (time() - $observer->getData('export')->getBeginTime())
                        )
                    );
                    $log->save();
                }
            }
        } catch (\Exception $e) {
            $this->ddoaLogger->error("Error while exporting shipment profile: " . $e->getMessage());
        }
    }
}
