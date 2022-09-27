<?php
/**
 * @package     Infosys/ShippingRestriction
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\ShippingRestriction\Plugin\StateFilter;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;

class StateFilter
{
    /**
     *
     * @var scopeConfig
     */
    protected $scopeConfig;
    /**
     *
     * @var allowedUsStates
     */
    protected $allowedUsStates;

    /**
     * Construct function
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Undocumented function
     *
     * @param Object $subject
     * @param array $options
     * @return array
     */
    public function afterToOptionArray($subject, $options)
    {
        $xmlPath = 'checkout/state_filter/us_state_filter';
        $allowedStates = $this->scopeConfig->getValue($xmlPath, ScopeInterface::SCOPE_STORE);
        $this->allowedUsStates = explode(",", $allowedStates);
        $result = array_filter($options, function ($option) {
            if (isset($option['value'])) {
                return in_array($option['value'], $this->allowedUsStates);
            }
            return true;
        });
        return $result;
    }
}
