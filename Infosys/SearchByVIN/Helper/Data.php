<?php
/**
 * @package Infosys/SearchByVIN
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\SearchByVIN\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class to get configuration values
 */
class Data extends AbstractHelper
{
    /**
     * Method to get configuration values
     *
     * @param string $config_path
     * @return string
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
