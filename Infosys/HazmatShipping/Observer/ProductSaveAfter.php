<?php

/**
 * @package   Infosys/HazmatShipping
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\HazmatShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Infosys\ProductSaleable\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Event\Observer;

/**
 * Class to update shipperhq shipping group and status of a product
 */
class ProductSaveAfter implements ObserverInterface
{

    protected Product $product;

    protected Action $action;

    protected Data $helper;

    /**
     * Constructor function
     *
     * @param Product $product
     * @param Action $action
     * @param Data $helper
     */
    public function __construct(
        Product $product,
        Action $action,
        Data $helper
    ) {
        $this->product = $product;
        $this->action = $action;
        $this->helper = $helper;
    }

    /**
     * Method to update Product Attributes
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        $product = $observer->getProduct();
        $stores = $product->getStoreIds();
        array_unshift($stores, '0');

        //Updating shipperhq shipping group based on hazmat flag
        $hazmatFlag = $product->getHazmatFlag();
        $doNotShip = $product->getDoNotShip();

        if ($hazmatFlag == 'Y' || $doNotShip== 'Y') {
            $optionData = $this->getOptionId('Do Not Ship');
        } else {
            $optionData = $this->getOptionId('');
        }

        $product->setData('shipperhq_shipping_group', $optionData);

        //Updating Product Status based on threshold price
        $threshold_price = $this->helper->getThresholdPrice();
        if ($threshold_price) {
            if ($product->getPrice() <= $threshold_price) {
                $product->setStatus(Status::STATUS_DISABLED);
                $updateAttributes['status'] = Status::STATUS_DISABLED;
                foreach ($stores as $storeId) {
                    $this->action->updateAttributes([$product->getId()], $updateAttributes, $storeId);
                }
            } else {
                $product->setStatus(Status::STATUS_ENABLED);
                $updateAttributes['status'] = Status::STATUS_ENABLED;
                foreach ($stores as $storeId) {
                    $this->action->updateAttributes([$product->getId()], $updateAttributes, $storeId);
                }
            }
        }
        $product->save();
    }

    /**
     * Method to get option id
     *
     * @param string $text
     */
    public function getOptionId($text): string
    {
        $attribute = $this->product->getResource()->getAttribute('shipperhq_shipping_group');
        if ($attribute->usesSource()) {
            $optionID = $attribute->getSource()->getOptionId($text);
        }
        return $optionID;
    }
}
