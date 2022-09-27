<?php

/**
 * @package   Infosys/Search
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Search\Model\Resolver\Magento;

use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Product implements ResolverInterface
{
    /**
     * @var AttributesJoiner
     */
    private $attributesJoiner;
    /**
     * Constructor function
     *
     * @param AttributesJoiner $attributesJoiner
     */
    public function __construct(
        AttributesJoiner $attributesJoiner
    ) {
        $this->attributesJoiner = $attributesJoiner;
    }
    /**
     * Resolver function
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $collection = $value['collection'];
        $pageSize = $args['pageSize'];
        $page = $args['currentPage'];

        $collection->setPageSize($pageSize);

        if ($page > $collection->getLastPageNumber()) {
            $page = $collection->getLastPageNumber();
        }

        $currentPage = $page;
        $pageSize = $pageSize;
        $firstItem = (($currentPage - 1) * $pageSize + 1);
        $lastItem = $firstItem + ($pageSize - 1);
        $iterator = 1;
        foreach ($collection as $key => $item) {
            $pos = $iterator;
            $iterator++;
            if ($pos >= $firstItem && $pos <= $lastItem) {
                continue;
            }
            $collection->removeItemByKey($key);
        }
        $items = [];

        foreach ($collection as $product) {
            $productData = $product->getData();
            $productData['model'] = $product;
            $items[] = $productData;
        }

        return $items;
    }
}
