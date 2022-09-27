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
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use StripeIntegration\Payments\Helper\Generic;

class CardList implements ResolverInterface
{
    /**
     * @var StripeCustomer\Collection
     */
    protected $customerCollection;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Generic
     */
    private $generic;

    /**
     * Constuctor function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Generic $generic
     * @param \StripeIntegration\Payments\Model\ResourceModel\StripeCustomer\Collection $customerCollection
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Generic $generic,
        \StripeIntegration\Payments\Model\ResourceModel\StripeCustomer\Collection $customerCollection,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->generic = $generic;
        $this->customerCollection = $customerCollection;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {

        $customerId = $context->getUserId();
        $stores = $this->storeRepository->getList();
        $allCards = [];
        foreach ($stores as $store) {
            $websiteId = (int)$this->storeManager->getStore($store->getId())->getWebsiteId();
            $websiteName = $this->storeManager->getWebsite($websiteId)->getName();
            $cardsDetails = $this->getCardsDetails($customerId, $store->getId());
            if ($cardsDetails) {
                foreach ($cardsDetails as $cardsDetail) {
                    $array = [
                        'cc_brand' => $cardsDetail->brand,
                        'cc_country' => $cardsDetail->country,
                        'cc_last4' => $cardsDetail->last4,
                        'cc_exp_year' => $cardsDetail->exp_year,
                        'cc_exp_month' => $cardsDetail->exp_month,
                        'cc_type' => $cardsDetail->funding,
                        'token' => $cardsDetail->id,
                        'website_name' => $websiteName,
                        'website_id' => $websiteId
                    ];

                    $allCards[] = $array;
                }
            }
        }

        return $allCards;
    }

    /**
     * Fetch customer card details
     *
     * @param string $customerId
     * @param int $storeId
     * @return array
     */
    public function getCardsDetails($customerId, $storeId)
    {
        $pk = $this->getPublishableKey($storeId);
        $model = null;
        if ($pk) {
            $model = $this->customerCollection->getByCustomerId($customerId, trim($pk));
            if ($model && $model->getId()) {
                $stripeCustomer = \Stripe\Customer::retrieve($model->getStripeId());
                $cards = $this->generic->listCards($stripeCustomer);
                return $cards;
            }
        }
    }

    /**
     * Return publishable key
     *
     * @param int $storeId
     * @return string
     */
    public function getPublishableKey($storeId)
    {
        $mode = $this->scopeConfig->getValue(
            "payment/stripe_payments_basic/stripe_mode",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $pk = $this->scopeConfig->getValue(
            "payment/stripe_payments_basic/stripe_{$mode}_pk",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $pk;
    }
}
