<?php

/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\PriceAdjustment\Cron;

use Infosys\Vehicle\Model\Config\Brand\BrandDataProvider;
use Magento\Framework\App\ResourceConnection;
use Infosys\PriceAdjustment\Model\Config\Source\SetProductTypeOptions;
use Infosys\PriceAdjustment\Model\BrandProductTypeFactory;
use Infosys\PriceAdjustment\Helper\Data;

/**
 * Class to update media set in dealer pricing model
 */
class UpdateMediaSetSelector
{
    public BrandDataProvider $brandDataProvider;
    
    public ResourceConnection $resourceConnection;
    
    public SetProductTypeOptions $attributeSet;
    
    public BrandProductTypeFactory $brandType;
    
    public Data $priceHelper;
    /**
     * @param BrandDataProvider $brandDataProvider
     * @param ResourceConnection $resourceConnection
     * @param SetProductTypeOptions $attributeSet
     * @param BrandProductTypeFactory $brandType
     * @param Data $priceHelper
     */
    public function __construct(
        BrandDataProvider $brandDataProvider,
        ResourceConnection $resourceConnection,
        SetProductTypeOptions $attributeSet,
        BrandProductTypeFactory $brandType,
        Data $priceHelper
    ) {
        $this->brandDataProvider = $brandDataProvider;
        $this->resourceConnection = $resourceConnection;
        $this->attributeSet = $attributeSet;
        $this->brandType = $brandType;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Function to update media set in dealer pricing model
     */
    public function execute(): bool
    {
        if (!$this->priceHelper->isCronEnabled()) {
            return false;
        }
        $brand = [];
        $result = [];
        $finalBrand = [];
        $finalattributeSetId = [];
        $brand = $this->brandDataProvider->toOptionArray();
        foreach ($brand as $mediaSetBrand) {
            $finalBrand[] = $mediaSetBrand['value'];
        }
        $attributeSetIds = $this->attributeSet->getAllOptions();
        foreach ($attributeSetIds as $attributeSetId) {
             $finalAttributeSetId[] = $attributeSetId['value'];
        }
        $conn = $this->resourceConnection->getConnection();
        $sql = $conn->select()->from(['p' => 'catalog_product_entity'], ['tier_price_set.value','p.attribute_set_id','v.brand'])
                ->join(['vp' => 'catalog_vehicle_product'], 'vp.product_id = p.entity_id', [''])
                ->join(['v' => 'catalog_vehicle_entity'], 'vp.vehicle_id = v.entity_id', [''])
                ->join(['tier_price_set' => 'catalog_product_entity_text'], "tier_price_set.row_id = p.entity_id", [''])
                ->join(['att_tier_price_set' => 'eav_attribute'], "att_tier_price_set.attribute_id = tier_price_set.attribute_id", [''])
                ->where('att_tier_price_set.attribute_code = (?)', 'tier_price_set')
                ->where('v.brand IN (?) ', $finalBrand)->where('p.attribute_set_id IN (?)', $finalAttributeSetId)->distinct(true);
        $data = $conn->fetchAll($sql);
        
        if (!empty($data)) {
            foreach ($data as $mediaSet) {
                $brandModel = $this->brandType->create();
                $checkAvailable = $brandModel->getCollection()
                ->addFieldToFilter('media_set_selector', $mediaSet['value'])
                ->addFieldToFilter('product_type', $mediaSet['attribute_set_id'])
                ->addFieldToFilter('dealer_brand', $mediaSet['brand'])
                ->getData();
                if (empty($checkAvailable)) {
                    $brandModel->setData('product_type', $mediaSet['attribute_set_id']);
                    $brandModel->setData('media_set_selector', $mediaSet['value']);
                    $brandModel->setData('dealer_brand', $mediaSet['brand']);
                    $brandModel->save();
                }
            }
        }
        return true;
    }
}
