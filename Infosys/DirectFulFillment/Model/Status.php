<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Model;

use \Xtento\TrackingImport\Model\Import\Action\Order\Status as XtentoStatus;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\ConfigFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Xtento\TrackingImport\Model\Processor\Mapping\Action\Configuration;
use Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses;
use Infosys\DirectFulFillment\Model\FreightRecoveryFactory;
use Infosys\DealerShippingCost\Logger\ShippingCostLogger;

class Status extends XtentoStatus
{
    /**
     * @var FreightRecoveryFactory
     */
    protected $freightRecoveryFractory;

    protected ShippingCostLogger $shippingCostLogger;

    /**
     * Constructor function
     *
     * @param Context $context
     * @param Registry $registry
     * @param Configuration $actionConfiguration
     * @param AllStatuses $orderStatuses
     * @param ConfigFactory $orderConfigFactory
     * @param OrderCommentSender $orderCommentSender
     * @param FreightRecoveryFactory $freightRecoveryFactory
     * @param ShippingCostLogger $shippingCostLogger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Configuration $actionConfiguration,
        AllStatuses $orderStatuses,
        ConfigFactory $orderConfigFactory,
        OrderCommentSender $orderCommentSender,
        FreightRecoveryFactory $freightRecoveryFactory,
        ShippingCostLogger $shippingCostLogger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $registry,
            $actionConfiguration,
            $orderStatuses,
            $orderConfigFactory,
            $orderCommentSender,
            $resource,
            $resourceCollection,
            $data
        );
        $this->freightRecoveryFactory = $freightRecoveryFactory;
        $this->shippingCostLogger = $shippingCostLogger;
    }

    /**
     * Overriding the method to include custom Fee action
     *
     * @return void
     */
    public function update()
    {
        $result = parent::update();
        $order = $this->getOrder();
        $updateData = $this->getUpdateData();
        try {
            if ($this->getActionSettingByField('order_update_fee', 'enabled')) {
                $this->setHasUpdatedObject(true);
                if (isset($updateData['service_fee'])) {
                    $order->setServiceFee($updateData['service_fee']);
                }
                if (isset($updateData['items'])) {
                    foreach ($updateData['items'] as $item) {
                        if (isset($item['freight_recovery'])) {
                            $freightRecovery = $this->freightRecoveryFactory->create();
                            $collection = $freightRecovery->getCollection()->addFieldToSelect('*')
                                ->addFieldToFilter('order_id', ['eq' => $order->getId()])
                                ->addFieldToFilter('action', ['eq' => 'direct_fulfillment'])
                                ->addFieldToFilter('freight_recovery', ['null' => true]);
                            if ($collection->count()) {
                                $shipmentItem = $collection->getFirstItem();
                                $shipmentItem->setCreatedAt(date_default_timezone_get());
                                $shipmentItem->setFreightRecovery($item['freight_recovery']);
                                $shipmentItem->save();
                            }
                        }
                    }
                }
                $order->save();
                return true;
            }
        } catch (\Exception $e) {
            $this->shippingCostLogger->error("Error in Direct Fulfillment Shipping Cost update" . $e);
        }
        return $result;
    }
}
