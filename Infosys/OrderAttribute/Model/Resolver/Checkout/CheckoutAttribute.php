<?php

/**
 * @package     Infosys/OrderAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\OrderAttribute\Model\Resolver\Checkout;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class to update Quote items data at checkout
 */
class CheckoutAttribute implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    protected $maskedQuoteIdToQuoteId;

    /**
     * Constructor function
     *
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }
    /**
     * Get quote items attribute value
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return string
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $cartId = $args['input']['cartId'];
        $items = $args['input']['cart_items'];
        $quoteId = $this->getQuoteIdFromMaskedHash($cartId);
        $quote = $this->quoteRepository->get($quoteId);
        $quoteItems = $quote->getAllVisibleItems();
        foreach ($quoteItems as $quoteItem) {
            foreach ($items as $item) {
                $itemId = $item['item_id'];
                if ($itemId == $quoteItem->getItemId()) {
                    $quoteItem->setFitmentNotice($item['fitment_message']);
                    $quoteItem->setVinNumber($item['vin_number']);
                    $quoteItem->setVehicleName($item['vehicle_name']);
                    $quoteItem->setFitmentStatus($item['fitment_status']);
                }
                $quoteItem->save();
            }
        }
        return ['output' => 'Updated'];
    }

    /**
     * Method to get quote Id from cart Id
     *
     * @param int $cartId
     * @return void
     */
    public function getQuoteIdFromMaskedHash($cartId)
    {
        return $this->maskedQuoteIdToQuoteId->execute($cartId);
    }
}
