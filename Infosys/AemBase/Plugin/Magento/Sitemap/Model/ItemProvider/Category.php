<?php
declare(strict_types=1);
/**
 * @package     Infosys/AemBase
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\AemBase\Plugin\Magento\Sitemap\Model\ItemProvider;

use Closure;

/**
 * Class Category
 *
 * Removes Category pages from sitemap
 */
class Category
{
    /**
     * @param \Magento\Sitemap\Model\ItemProvider\Category $subject
     * @param Closure $proceed
     * @return array
     *
     * Around plugin used because we want to prevent execution of original function
     */
    public function aroundGetItems(
        \Magento\Sitemap\Model\ItemProvider\Category $subject,
        Closure $proceed
    ) {
        return [];
    }
}
