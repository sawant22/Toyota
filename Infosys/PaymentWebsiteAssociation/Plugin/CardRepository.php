<?php

/**
 * @package     Infosys/PaymentWebsiteAssociation
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\PaymentWebsiteAssociation\Plugin;

use Magento\Store\Model\StoreManagerInterface;

class CardRepository
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * Constructor function
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }
    /**
     * Save website id along with card details
     *
     * @param \ParadoxLabs\TokenBase\Model\ResourceModel\CardRepository $subject
     * @param \ParadoxLabs\TokenBase\Api\Data\CardInterface $card
     * @return void
     */
    public function beforeSave(
        \ParadoxLabs\TokenBase\Model\ResourceModel\CardRepository $subject,
        \ParadoxLabs\TokenBase\Api\Data\CardInterface $card
    ) {
        if (!$card->getWebsiteId()) {
            $card->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
        }
        return [$card];
    }
}
