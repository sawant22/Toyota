<?php

/**
 * @package     Infosys/ProductAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare (strict_types = 1);

namespace Infosys\ProductAttribute\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Data patch to remove custom attributes
 */
class RemoveCustomAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        try {
            //Remove custom attributes
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $attributes = ['catalog_id','model_year_description','production_end_date','trim','production_start_date',
            'wmi','color_id','model_year_edition','katashiki_code','katashiki_description','make',
            'category_description','sub_category_description','marketing_category_description','part_name_code',
            'product_code_description','detail_category','part_type','part_disclaimer_text','stop_sale_indicator',
            'part_expiration_date','part_live_date','major_category','substitution_part_name','substitution_part_group',
            'marketing_category','customer_text','attribute_id','attribute_text','fob','labor_rate','labor_time',
            'version','primary_image_indicator','detail_image','alt_text','url','voice_search','live_search',
            'supersession','trd','product_video','vehicle_variant','model_year_specification','marketing_brand',
            'fits','emissions_legal','california_notice_marker','in_store_pick_up_availability'];
            foreach ($attributes as $attribute) {
                $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
