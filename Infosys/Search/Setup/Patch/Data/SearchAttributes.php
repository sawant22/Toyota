<?php

/**
 * @package     Infosys/Search
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Search\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Infosys\ProductAttribute\Setup\Patch\Data\ProductAttribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class SearchAttributes implements DataPatchInterface
{
    /**
     * Constructor function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Product $product
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Product $product
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->product = $product;
    }

    /**
     * Patch to create Product Attributes and update search weight
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $eavSetup = $this->eavSetupFactory->create();
        $attributeSetId = $this->product->getDefaultAttributeSetId();
        $eavSetup->updateAttribute(Product::ENTITY, 'part_name_code', 'search_weight', '6');
        $eavSetup->addAttribute(
            Product::ENTITY,
            'part_number',
            [
                // Let empty, if we want to set an attribute group id
                'group' => '',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Part Number',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'search_weight' => 6,
                'apply to' => ''
            ]
        );
        $attributeGroupId = $eavSetup->getAttributeGroupId(Product::ENTITY, $attributeSetId, 'Part');
        $eavSetup->addAttributeToGroup(Product::ENTITY, $attributeSetId, $attributeGroupId, 'part_number');
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Dependencies function
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [ProductAttribute::class];
    }
    /**
     * Aliases function
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
