<?php

/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\PriceAdjustment\Controller\Adminhtml\Percentage;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Infosys\PriceAdjustment\Model\MediaFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Infosys\PriceAdjustment\Model\TierFactory;
use Infosys\PriceAdjustment\Model\TierQueueFactory;
use Infosys\PriceAdjustment\Publisher\TierPriceImport as Publisher;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ResourceConnection;
use Infosys\PriceAdjustment\Helper\Data;

class Save extends Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    protected const ADMIN_RESOURCE = 'Infosys_PriceAdjustment::entity';
     
    protected MediaFactory $mediaFactory;

    protected tierFactory $_tierFactory;

    protected ScopeConfigInterface $scopeConfig;
     
    protected $messageManager;
    
    protected ResourceConnection $resource;
     
    private TierQueueFactory $tierQueueFactory;
    
    public Json $serializer;
     
    private Publisher $publisher;
    
    protected Data $data;
    /**
     * @param Context $context
     * @param MediaFactory $mediaFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param TierFactory $tierModel
     * @param ResourceConnection $resourceConnection
     * @param TierQueueFactory $tierQueueFactory
     * @param Publisher $publisher
     * @param Json $serializer
     * @param Data $data
     */
    public function __construct(
        Context $context,
        MediaFactory $mediaFactory,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        TierFactory $tierModel,
        ResourceConnection $resourceConnection,
        TierQueueFactory $tierQueueFactory,
        Publisher $publisher,
        Json $serializer,
        Data $data
    ) {
        parent::__construct($context);
        $this->mediaFactory = $mediaFactory;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->tierFactory = $tierModel;
        $this->resource = $resourceConnection;
        $this->tierQueueFactory = $tierQueueFactory;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->data = $data;
    }

    /**
     * Save action
     */
    public function execute()
    {
        $resultRedirect     = $this->resultRedirectFactory->create();
        $mediaModel        = $this->mediaFactory->create();
        $data               = $this->getRequest()->getPost();

        try {
            if (!empty($data['entity_id'])) {
                $mediaModel->setData('entity_id', $data['entity_id']);
            }

            $maxPercentage = $this->scopeConfig->getValue("discount/discount_configuration/max_percentage");
            $minPercentage = $this->scopeConfig->getValue("discount/discount_configuration/min_percentage");

            if (empty($maxPercentage) || empty($minPercentage)) {
                $this->messageManager->addErrorMessage(__("Please set the max and min value fast"));
                return $resultRedirect->setPath('*/*/edit', [
                    'entity_id' => $mediaModel->getId(),
                    '_current' => true, '_use_rewrite' => true
                ]);
            }

            $checkAvailable = $mediaModel->getCollection()->addFieldToFilter(
                'media_set_selector',
                $data['media_set_selector']
            )
                ->addFieldToFilter('website', $data['website'])
                ->addFieldToFilter('tier_price_product_type', $data['tier_price_product_type'])
                ->getData();

            if (empty($data['entity_id']) && !empty($checkAvailable)) {
                $this->messageManager->addErrorMessage(__("Media set already exist for this website"));
                return $resultRedirect->setPath('*/*/edit', [
                    'entity_id' => $mediaModel->getId(),
                    '_current' => true, '_use_rewrite' => true
                ]);
            }
            if ($data['entity_id']) {
                $checkAvailables = $mediaModel->getCollection()->addFieldToFilter(
                    'media_set_selector',
                    $data['media_set_selector']
                )->addFieldToFilter('website', $data['website'])
                    ->addFieldToFilter('tier_price_product_type', $data['tier_price_product_type'])
                    ->addFieldToFilter('entity_id', ['neq' => $data['entity_id']])
                    ->getData();

                if (!empty($checkAvailables)) {
                    $this->messageManager->addErrorMessage(__("Media set already exist for this website"));
                    return $resultRedirect->setPath('*/*/edit', [
                        'entity_id' => $mediaModel->getId(),
                        '_current' => true, '_use_rewrite' => true
                    ]);
                }
            }
            $mediaModel->setData('media_set_selector', $data['media_set_selector']);
            $mediaModel->setData('website', $data['website']);
            $mediaModel->setData('tier_price_product_type', $data['tier_price_product_type']);
            $mediaModel->save();
            $dynamicRowData   = $this->getRequest()->getParam('mediaset_percentage_form_container');
            $checkDuplicates = [];
            if (is_array($dynamicRowData) && !empty($dynamicRowData)) {
                foreach ($dynamicRowData as $dynamicRowDatum) {
                    if ($dynamicRowDatum['to_price'] <= $dynamicRowDatum['from_price']) {
                        $this->messageManager->addErrorMessage(__("To price can't be less than from price"));
                        return $resultRedirect->setPath('*/*/edit', [
                            'entity_id' => $mediaModel->getId(),
                            '_current' => true, '_use_rewrite' => true
                        ]);
                    }

                    if (!empty($checkDuplicates)) {
                        if ($dynamicRowDatum['from_price'] >= $checkDuplicates[0] && $dynamicRowDatum['from_price'] <= $checkDuplicates[1]) {
                            $this->messageManager->addErrorMessage(__("This price is already in price range"));
                            return $resultRedirect->setPath('*/*/edit', [
                                'entity_id' => $mediaModel->getId(),
                                '_current' => true, '_use_rewrite' => true
                            ]);
                        }
                        if ($dynamicRowDatum['to_price'] >= $checkDuplicates[0] && $dynamicRowDatum['to_price'] <= $checkDuplicates[1]) {
                            $this->messageManager->addErrorMessage(__("This price is already in price range"));
                            return $resultRedirect->setPath('*/*/edit', [
                                'entity_id' => $mediaModel->getId(),
                                '_current' => true, '_use_rewrite' => true
                            ]);
                        }

                        if ($dynamicRowDatum['from_price'] <= $checkDuplicates[0]) {
                            $arr = [$dynamicRowDatum['from_price'], $checkDuplicates[1]];
                            $checkDuplicates = array_replace($checkDuplicates, $arr);
                        }
                        if ($dynamicRowDatum['to_price'] >= $checkDuplicates[1]) {
                            $arr = [$checkDuplicates[0], $dynamicRowDatum['to_price']];
                            $checkDuplicates = array_replace($checkDuplicates, $arr);
                        }
                    } else {
                        array_push($checkDuplicates, $dynamicRowDatum['from_price'], $dynamicRowDatum['to_price']);
                    }

                    if ($dynamicRowDatum['adjustment_type'] == 1 && $dynamicRowDatum['percentage'] < $minPercentage) {
                        $this->messageManager->addErrorMessage(__("for cost+percentage Price can't be less than " . $minPercentage));
                        return $resultRedirect->setPath('*/*/edit', [
                            'entity_id' => $mediaModel->getId(),
                            '_current' => true, '_use_rewrite' => true
                        ]);
                    }
                    if ($dynamicRowDatum['adjustment_type'] == 2 && $dynamicRowDatum['percentage'] > $maxPercentage) {
                        $this->messageManager->addErrorMessage(__("for MSRP - percentage Price can't be greater than " . $maxPercentage));
                        return $resultRedirect->setPath('*/*/edit', [
                            'entity_id' => $mediaModel->getId(),
                            '_current' => true, '_use_rewrite' => true
                        ]);
                    }
                }
            }
            $this->deleteDynamicRows($mediaModel->getData('entity_id'));
            if (is_array($dynamicRowData) && !empty($dynamicRowData)) {
                foreach ($dynamicRowData as $dynamicRowDatum) {
                    unset($dynamicRowDatum['id']);
                    $tierModel = $this->tierFactory->create();
                    $tierModel->addData($dynamicRowDatum);
                    $tierModel->setData('entity_id', $mediaModel->getData('entity_id'));
                    $tierModel->setData('website', $data['website']);
                    $tierModel->save();
                }
            }

            $websiteId = $data['website'];
            $tierPriceSet = $data['media_set_selector'];
            $tierPriceProductType = $data['tier_price_product_type'];
            if ($this->data->isRabbitMQEnabled()) {
                $tierMediaSet['website'] = $websiteId;
                $tierMediaSet['tier_price_set'] = $tierPriceSet;
                $tierMediaSet['tier_price_product_type'] = $tierPriceProductType;
                $tierMediaSet = $this->serializer->serialize($tierMediaSet);
                $this->publisher->publish($tierMediaSet);
            } else {
                $tierQueue = $this->tierQueueFactory->create();
                $tierCollection = $tierQueue->getCollection()
                    ->addFieldToFilter('sku', "")
                    ->addFieldToFilter('website', $websiteId)
                    ->addFieldToFilter('tier_price_set', $tierPriceSet)
                    ->addFieldToFilter('special_price_update_status', "N")
                    ->addFieldToFilter('tier_price_product_type', $tierPriceProductType)
                    ->getData();

                //if it doesn't exist, insert it.
                if (empty($tierCollection)) {
                    $tierQueue->setData('sku', "");
                    $tierQueue->setData('website', $websiteId);
                    $tierQueue->setData('tier_price_set', $tierPriceSet);
                    $tierQueue->setData('special_price_update_status', "N");
                    $tierQueue->setData('tier_price_product_type', $tierPriceProductType);
                    $tierQueue->setData('old_product_price', '');
                    $tierQueue->setData('old_tierprice_id', '');
                    $tierQueue->save();
                }
            }
            //check for `back` parameter
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', [
                    'entity_id' => $mediaModel->getId(),
                    '_current' => true, '_use_rewrite' => true
                ]);
            }

            $this->messageManager->addSuccessMessage(__('New Prices are being calculated.'));
            $this->_redirect('*/*');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }

    /**
     * Delete dynamic rows based on entity id
     *
     * @param int $id
     */
    public function deleteDynamicRows($id): void
    {
        $connection = $this->resource->getConnection();
        $myTable = $connection->getTableName('tier_price');
        $connection->delete(
            $myTable,
            ['entity_id = ?' => $id]
        );
    }
}
