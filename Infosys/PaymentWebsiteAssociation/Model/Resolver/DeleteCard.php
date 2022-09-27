<?php

/**
 * @package     Infosys/PaymentWebsiteAssociation
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\PaymentWebsiteAssociation\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;

class DeleteCard implements ResolverInterface
{
    /**
     * @var Generic
     */
    protected $helper;

    /**
     * Constuctor function
     * @param \StripeIntegration\Payments\Helper\Generic $helper
     */
    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper
    ) {
        $this->helper = $helper;
        $this->stripeCustomer = $helper->getCustomerModel();
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $token = $args['hash'];

        $msg = '';
        try {
            $customerId = $context->getUserId();
            $statuses = ['processing', 'fraud', 'pending_payment', 'payment_review', 'pending', 'holded'];
            $orders = $this->helper->getCustomerOrders($customerId, $statuses, $token);
            foreach ($orders as $order) {
                $message = __(
                    "Sorry, it is not possible to delete this card because order
                     #%1 which was placed using this card is still being processed.",
                    $order->getIncrementId()
                );
                throw new LocalizedException($message);
            }

            $card = $this->stripeCustomer->deleteCard($token);

            // In case we deleted a source
            if (isset($card->card)) {
                $card = $card->card;
            }

            $msg = __("Card **** %1 has been deleted.", $card->last4);
        } catch (LocalizedException $e) {
            $msg = $e->getMessage();
        } catch (\Stripe\Exception\CardException $e) {
            $msg = $e->getMessage();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        return $msg;
    }
}
