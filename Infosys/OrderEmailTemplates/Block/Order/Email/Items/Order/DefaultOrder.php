<?php
/**
 * @package   Infosys/OrderEmailTemplates
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\OrderEmailTemplates\Block\Order\Email\Items\Order;

use Magento\Sales\Model\Order\Item as OrderItem;
 
class DefaultOrder extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * Returns Product Part Number for Item provided
     *
     * @param OrderItem $item
     * @return mixed
     */
    public function getPartNumber($item)
    {
        $product = $item->getProduct();
        $partNumber = '';
        if ($product->getPartNumber()) {
             $partNumber =$product->getPartNumber();
        }
        return $partNumber;
    }
}
