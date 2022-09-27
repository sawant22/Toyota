<?php
/**
 * @package     Infosys/UnsubscribeEmailToNewsletter
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\UnsubscribeEmailToNewsletter\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Resolver class for the `unsubscribeEmailToNewsletter` mutation. Adds an email into a newsletter subscription.
 */
class UnSubscribeEmailToNewsletter implements ResolverInterface
{
    private const STATUS_UNSUBSCRIBED = 3;
    
    /**
     * @var SubscriberFactory
     */
    private $_subscriberFactory;

    /**
     * SubscribeEmailToNewsletter constructor.
     *
     * @param SubscriberFactory $_subscriberFactory
     */
    public function __construct(
        SubscriberFactory $_subscriberFactory
    ) {
        $this->_subscriberFactory = $_subscriberFactory;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $email = trim($args['email']);

        if (empty($email)) {
            throw new GraphQlInputException(
                __('You must specify an email address to subscribe to a newsletter.')
            );
        }
        
        $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
        
        if (!$subscriber->isSubscribed()) {
            throw new GraphQlInputException(
                __('Email id does not subscribed yet')
            );
        }
        $subscriber->setSubscriberStatus(self::STATUS_UNSUBSCRIBED)->save();
        $subscriber->sendUnsubscriptionEmail();
        return ['status' => 'success'];
    }
}
