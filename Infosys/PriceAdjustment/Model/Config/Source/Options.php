<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model\Config\Source;

use Infosys\PriceAdjustment\Model\MediaFactory;

class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var MediaFactory
     */
    private $mediaFactory;
    
    /**
     * @param MediaFactory $mediaFactory
     */
    public function __construct(
        MediaFactory $mediaFactory
    ) {
        $this->mediaFactory = $mediaFactory;
    }
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {

        $media = $this->mediaFactory->create();
        $collection = $media->getCollection();

        $this->_options[] = ['label' => __('-- Select --'), 'value'=> ''];
        foreach ($collection as $item) {
            $this->_options[] = ['label' => __($item->getTitle()), 'value'=> $item->getPercentage()];
        }

        return $this->_options;
    }
}
