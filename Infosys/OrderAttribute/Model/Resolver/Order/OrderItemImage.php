<?php

/**
 * @package     Infosys/OrderAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\OrderAttribute\Model\Resolver\Order;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManager;

/**
 * Class to get order item image
 */
class OrderItemImage implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * Constructor function
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param StoreManager $storeManager
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        StoreManager $storeManager
    ) {
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Get order item attribute value
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return void
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $orderItem = $value['model'];
        $image_url = '';
        if ($orderItem) {
            $product = $orderItem->getProduct();
            if ($product) {
                if ($product->getImage() == "no_selection" || empty($product->getImage())) {
                    $mediaUrl = $this->storeManager->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                    $image_url = $mediaUrl . 'catalog/product/placeholder/' . $this->getConfig('catalog/placeholder/thumbnail_placeholder');
                } else {
                    $_product = $this->productRepository->getById($product->getData('entity_id'));
                    $image_url = $_product->getData('image');
                }
            }
        }
        return $image_url;
    }

    /**
     * Method to get store config data
     *
     * @param string $config_path
     * @return void
     */
    public function getConfig($config_path)
    {
        return $this->storeManager->getStore()->getConfig($config_path);
    }
}
