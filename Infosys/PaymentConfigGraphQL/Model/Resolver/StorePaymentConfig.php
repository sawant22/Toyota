<?php
/**
 * @package     Infosys/PaymentConfigGraphQL
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\PaymentConfigGraphQL\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Resolver class for dealer payment configuration
 */
class StorePaymentConfig implements ResolverInterface
{
    protected ScopeConfigInterface $scopeConfig;

    protected Authorization $authorization;

    protected EncryptorInterface $encryptor;

    /**
     * Initialize dependencies
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Authorization $authorization
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Authorization $authorization,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->authorization = $authorization;
        $this->encryptor = $encryptor;
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
        
        //check if user has permissions
        if (!$this->authorization->isAllowed(['Magento_Payment::payment'])) {
            throw new GraphQlAuthorizationException(
                __("The consumer isn't authorized to access %resources.")
            );
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $paymentData = [];

        $stripe_mode = $this->scopeConfig->getValue(
            'payment/stripe_payments_basic/stripe_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    
        if ($stripe_mode == 'test') {
            $paymentData = [
                'pub_api_key' => $this->scopeConfig->getValue(
                    'payment/stripe_payments_basic/stripe_test_pk',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                ),
                'secret_api_key' => $this->encryptor->decrypt($this->scopeConfig->getValue(
                    'payment/stripe_payments_basic/stripe_test_sk',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                ))
            ];
        } else {
            $paymentData = [
                'pub_api_key' => $this->scopeConfig->getValue(
                    'payment/stripe_payments_basic/stripe_live_pk',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                ),
                'secret_api_key' => $this->encryptor->decrypt($this->scopeConfig->getValue(
                    'payment/stripe_payments_basic/stripe_live_sk',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                ))
            ];
        }

        return $paymentData;
    }
}
