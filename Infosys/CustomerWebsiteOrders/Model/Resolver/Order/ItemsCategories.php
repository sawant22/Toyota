<?php
/**
 * @package     Infosys/CustomerWebsiteOrders
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CustomerWebsiteOrders\Model\Resolver\Order;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\CatalogGraphQl\Model\Category\Hydrator as CategoryHydrator;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductCategories;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CustomAttributesFlattener;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class to provide product categories in order success page.
 */
class ItemsCategories implements ResolverInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * Accumulated category ids
     *
     * @var array
     */
    private $categoryIds = [];

    /**
     * @var AttributesJoiner
     */
    private $attributesJoiner;

    /**
     * @var CustomAttributesFlattener
     */
    private $customAttributesFlattener;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var CategoryHydrator
     */
    private $categoryHydrator;

    /**
     * @var ProductCategories
     */
    private $productCategories;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CollectionFactory $collectionFactory
     * @param AttributesJoiner $attributesJoiner
     * @param CustomAttributesFlattener $customAttributesFlattener
     * @param ValueFactory $valueFactory
     * @param CategoryHydrator $categoryHydrator
     * @param ProductCategories $productCategories
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributesJoiner $attributesJoiner,
        CustomAttributesFlattener $customAttributesFlattener,
        ValueFactory $valueFactory,
        CategoryHydrator $categoryHydrator,
        ProductCategories $productCategories,
        StoreManagerInterface $storeManager
    ) {
        $this->collection = $collectionFactory->create();
        $this->attributesJoiner = $attributesJoiner;
        $this->customAttributesFlattener = $customAttributesFlattener;
        $this->valueFactory = $valueFactory;
        $this->categoryHydrator = $categoryHydrator;
        $this->productCategories = $productCategories;
        $this->storeManager = $storeManager;
    }

    /**
     * Resolver to provide product categories in customer orders query
     *
     * @param Field $field
     * @param Object $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Object
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $orderItem = $value['model'];
        if ($orderItem) {
            $product = $orderItem->getProduct();
            if ($product) {
                $storeId = $this->storeManager->getStore()->getId();
                $categoryIds = $this->productCategories->getCategoryIdsByProduct((int)$product->getId(), (int)$storeId);
                $this->categoryIds = array_merge($this->categoryIds, $categoryIds);
                $that = $this;

                return $this->valueFactory->create(
                    function () use ($that, $categoryIds, $info) {
                        $categories = [];
                        if (empty($that->categoryIds)) {
                            return [];
                        }

                        if (!$this->collection->isLoaded()) {
                            $that->attributesJoiner->join($info->fieldNodes[0], $this->collection, $info);
                            $this->collection->addIdFilter($this->categoryIds);
                        }
                        /** @var CategoryInterface | \Magento\Catalog\Model\Category $item */
                        foreach ($this->collection as $item) {
                            if (in_array($item->getId(), $categoryIds)) {
                                // Try to extract all requested fields from the loaded collection data
                                $categories[$item->getId()] = $this->categoryHydrator->hydrateCategory($item, true);
                                $categories[$item->getId()]['model'] = $item;
                                $requestedFields = $that->attributesJoiner->getQueryFields($info->fieldNodes[0], $info);
                                $extractedFields = array_keys($categories[$item->getId()]);
                                $foundFields = array_intersect($requestedFields, $extractedFields);
                                if (count($requestedFields) === count($foundFields)) {
                                    continue;
                                }
                                $categories[$item->getId()] = $this->categoryHydrator->hydrateCategory($item);
                            }
                        }
                        return $categories;
                    }
                );
            }
        }
    }
}
