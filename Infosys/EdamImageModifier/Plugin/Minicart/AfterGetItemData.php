<?php

/**
 * @package Infosys/EdamImageModifier
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\EdamImageModifier\Plugin\Minicart;

use Magento\Checkout\CustomerData\AbstractItem;

/**
 * Class to display image in mini cart
 */
class AfterGetItemData
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function __construct(\Magento\Catalog\Model\Product $product)
    {
        $this->product = $product;
    }

    /**
     * Prepare image
     *
     * @param AbstractItem $item
     * @param mixed $result
     * @return mixed
     */
    public function afterGetItemData(AbstractItem $item, $result)
    {
        try {
            if ($result['product_id'] > 0) {
                $product = $this->product->load($result['product_id']);
                $imageUrl = $product->getData('small_image');
                if (!empty($imageUrl)) {
                    $result['product_image']['src'] = $imageUrl;
                }
            }
        } catch (\Exception $e) {
            return $result;
        }
        return $result;
    }
}
