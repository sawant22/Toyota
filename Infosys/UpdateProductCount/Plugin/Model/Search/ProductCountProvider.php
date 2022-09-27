<?php
/**
 * @package     Infosys/UpdateProductCount
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\UpdateProductCount\Plugin\Model\Search;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Undocumented function
 *
 * @param Object $subject
 * @param array $options
 * @return array
 */
class ProductCountProvider
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

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
     *  Method to update max page size
     *
     * @param PageSizeProvider $subject
     * @param int $result
     * @return int
     */
    public function aftergetMaxPageSize(\Magento\Search\Model\Search\PageSizeProvider $subject, $result) : int
    {
        $pageSize = $this->scopeConfig->getValue(
            'updatecount/general/maximum_product_count',
            ScopeInterface::SCOPE_STORE
        );
        if ($pageSize) {
            return (int)$pageSize;
        }
        return (int)$result;
    }
}
