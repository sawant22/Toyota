<?php

/**
 * @package     Infosys/WishlistGraphQl
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\WishlistGraphQl\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Mapper\WishlistDataMapper;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory as WishlistCollectionFactory;

class CustomerWishlists
{
    /**
     * Constructor function
     *
     * @param WishlistFactory $wishlistFactory
     * @param WishlistDataMapper $wishlistDataMapper
     * @param WishlistCollectionFactory $wishlistCollectionFactory
     */
    public function __construct(
        WishlistFactory $wishlistFactory,
        WishlistDataMapper $wishlistDataMapper,
        WishlistCollectionFactory $wishlistCollectionFactory
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistDataMapper = $wishlistDataMapper;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
    }

    /**
     * Overriding the method create wishlist if it's not created
     *
     * @param \Magento\WishlistGraphQl\Model\Resolver\CustomerWishlists $subject
     * @param array $result
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return void
     */
    public function afterResolve(
        \Magento\WishlistGraphQl\Model\Resolver\CustomerWishlists $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($result)) {
            $wishlist = $this->wishlistFactory->create();
            $wishlist->loadByCustomerId($context->getUserId(), true);
            $collection = $this->wishlistCollectionFactory->create();
            $collection->filterByCustomerId($context->getUserId());
            foreach ($collection->getItems() as $wishList) {
                array_push($result, $this->wishlistDataMapper->map($wishList));
            }
        }
        return $result;
    }
}
