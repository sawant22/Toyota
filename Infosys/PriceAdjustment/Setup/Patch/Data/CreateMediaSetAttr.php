<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare (strict_types = 1);

namespace Infosys\PriceAdjustment\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateMediaSetAttr implements DataPatchInterface
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
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attribute = 'media_set_catalog';
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
        
        $eavSetup->addAttribute('catalog_product', 'tier_price_set', [
            'group' => 'Product Details',
            'input' => 'select',
            'type' => 'text',
            'label' => 'Tier Price Set',
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => true,
            'filterable' => true,
            'comparable' => false,
            'visible_on_front' => true,
            'visible_in_advanced_search' => true,
            'is_html_allowed_on_front' => false,
            'used_for_promo_rules' => true,
            'option' => ['values' => ['Toyota Accessories', 'Toyota Parts', 'Lexus Accessories', 'Lexus Parts']],
            'frontend_class' => '',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'unique' => false,
            'apply_to' => 'simple,grouped,configurable,downloadable,virtual,bundle'
        ]);
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
