<?php
declare(strict_types=1);
/**
 * @package     Infosys/AemBase
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\AemBase\Plugin\Magento\Sitemap\Model\ResourceModel\Catalog;

use Infosys\AemBase\Model\AemBaseConfigProvider;
use Magento\Framework\DataObject;

/**
 * Class Product
 *
 * Modifies Product URL path for AEM
 */
class Product
{

    /** @var AemBaseConfigProvider */
    protected $configProvider;

    /**
     * Product constructor.
     * @param AemBaseConfigProvider $configProvider
     */
    public function __construct(
        AemBaseConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\Product $subject
     * @param $result
     * @return mixed
     */
    public function afterGetCollection(
        \Magento\Sitemap\Model\ResourceModel\Catalog\Product $subject,
        $result
    ) {
        foreach ($result as $product) {
            /** @var DataObject $product */
            $productPath = $this->configProvider->getAemProductPath() . $product->getData('url');
            $product->setData('url', $productPath);
        }
        return $result;
    }
}
