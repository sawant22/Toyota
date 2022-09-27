<?php

/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\PriceAdjustment\Model\Config\Source;

use Magento\Catalog\Model\Product;
use Infosys\PriceAdjustment\Model\MediaFactory;

class SetGridMediaSetOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    
    private Product $product;
    
    protected MediaFactory $mediaFactory;

    /**
     * Constructor function
     *
     * @param Product $product
     * @param MediaFactory $mediaFactory
     */
    public function __construct(
        Product $product,
        MediaFactory $mediaFactory
    ) {
        $this->product = $product;
        $this->mediaFactory = $mediaFactory;
    }

    /**
     * Get all medias set options
     *
     * @return array
     */
    public function getAllOptions(): Array
    {
        $result =  $finalResult = $mediaSetSelector = [];
        $mediaModel = $this->mediaFactory->create();
        $media = $mediaModel->getCollection();
        foreach ($media as $mediaSet) {
            if (!in_array($mediaSet['media_set_selector'], $mediaSetSelector)) {
                $mediaSetSelector[] = $mediaSet['media_set_selector'];
                $result['label'] = $this->getOptionText($mediaSet['media_set_selector']);
                $result['value'] = $mediaSet['media_set_selector'];
                $finalResult[] = $result;
            }
        }
        return $finalResult;
    }

    /**
     * Fetch media set option value by option id
     *
     * @param int $option_id
     * @return string
     */
    public function getOptionText($option_id): string
    {
        $optionText = "";
        $attr = $this->product->getResource()->getAttribute('tier_price_set');
        if ($attr->usesSource()) {
            $optionText = $attr->getSource()->getOptionText($option_id);
        }
        return $optionText;
    }
}
