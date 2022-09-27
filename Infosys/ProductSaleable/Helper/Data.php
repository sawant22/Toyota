<?php

/**
 * @package Infosys/ProductSaleable
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\ProductSaleable\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PRICE_THRESHOLD = 'threshold_price_config/threshold_price_group/threshold_price';

    /**
     * Method to get product threshold price
     */
    public function getThresholdPrice(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PRICE_THRESHOLD,
            ScopeInterface::SCOPE_STORE
        );
    }
}
