<?php

/**
 * @package   Infosys/CustomOrderNumber
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\CustomOrderNumber\Helper;

/**
 * Customer order number helper class
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_IS_ENABLED = 'customordernumber/general/enabled';
    
    /**
     * Is Enabled 
     *
     */
    public function isEnabled(): bool
    {
        if ($this->getConfig(self::XML_IS_ENABLED)) {
            return true;
        }

        return false;
    }
    /**
     * Get Configuration Values
     *
     */
    public function getConfig($path, $storeId = null): string
    {
        $configVal = $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $storeId
        );
        return $configVal;
    }
}
