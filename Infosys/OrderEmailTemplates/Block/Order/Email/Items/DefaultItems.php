<?php
/**
 * @package   Infosys/OrderEmailTemplates
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\OrderEmailTemplates\Block\Order\Email\Items;

use Magento\Sales\Model\Order\Item as OrderItem;

class DefaultItems extends \Magento\Sales\Block\Order\Email\Items\DefaultItems
{
    /**
     * Returns Product Part Number for Item provided
     *
     * @param OrderItem $item
     * @return mixed
     */
    public function getPartNumber($item)
    {
        $product_id = $item->getProductId();
        $partNumber = '';
        //need to use obkect manager because if we use construct it is returning null
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
        if ($product->getPartNumber()) {
                $partNumber = $product->getPartNumber();
        }
        return $partNumber;
    }
}
