<?php
/**
 * @package     Infosys/AemBase
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\AemBase\Plugin\Product;

use Infosys\AemBase\Model\AemBaseConfigProvider;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Catalog\Model\Product;

class Url
{
    const PRODUCT_PATH = "products/product-page.";

    /** @var AemBaseConfigProvider */
    private $configProvider;

    /**
     * Url constructor.
     * @param AemBaseConfigProvider $configProvider
     */
    public function __construct(
        AemBaseConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * The product URL should not contain magento path since we want to use AEM path
     *
     * @param ProductUrl $subject
     * @param $result
     * @param Product $product
     * @param null $useSid
     * @return string|string[]
     */
    public function afterGetUrl(
        ProductUrl $subject,
        $result,
        Product $product,
        $useSid = null
    ) {
        $magentoPath = $this->configProvider->getMagentoPath();
        $productPath = $this->configProvider->getAemProductPath();

        $result = str_replace($magentoPath, $productPath, $result);
        return $result;
    }
}
