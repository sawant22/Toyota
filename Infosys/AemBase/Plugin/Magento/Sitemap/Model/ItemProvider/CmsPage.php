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
 * Class CmsPage
 *
 * Disable Adding CMS Pages to Sitemap
 */
class CmsPage
{

    /**
     * @param \Magento\Sitemap\Model\ItemProvider\CmsPage $subject
     * @param Closure $proceed
     * @return array
     *
     * Around plugin used because we want to prevent execution of original function
     */
    public function aroundGetItems(
        \Magento\Sitemap\Model\ItemProvider\CmsPage $subject,
        Closure $proceed
    ) {
        return [];
    }
}
