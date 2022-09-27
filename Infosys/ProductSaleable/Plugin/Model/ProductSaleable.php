<?php
/**
 * @package Infosys/ProductSaleable
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\ProductSaleable\Plugin\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventoryCatalog\Model\SourceItemsProcessor;

class ProductSaleable
{
    /**
     *
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * Construct function
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     *
     * @param SourceItemsProcessor $subject
     * @param mixed $sku
     * @param mixed $sources
     *
     * @return array
     */
    public function beforeExecute( SourceItemsProcessor $subject, $sku, $sources ) {
        $product = $this->productRepository->get( $sku );
        $saleable = $product->getSaleable();

        foreach ( $sources as $key => $source ) {
            if ($saleable == 'Y') {
                $sources[$key]['status'] = true;
            }elseif ($saleable == 'N') {
                $sources[$key]['status'] = false;
            }
        }

        return [$sku, $sources];
    }
}
