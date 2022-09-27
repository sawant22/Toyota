<?php
/**
 * @package     Infosys/ProductAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\ProductAttribute\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Product attribute data patch
 */
class DisclaimerTextUpdate implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    
    /**
     * Constuctor function
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
     * Patch to create Product Attributes
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->updateProductAttribute();
        $this->moduleDataSetup->endSetup();
    }
   
    /**
     * Method to update Attributes
     *
     * @return void
     */
    private function updateProductAttribute()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityType = $eavSetup->getEntityTypeId('catalog_product');
        $eavSetup->updateAttribute($entityType, 'disclaimer_text', 'backend_type', 'text', null);
        $eavSetup->updateAttribute($entityType, 'disclaimer_text', 'is_wysiwyg_enabled', 1, null);
    }
      
    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [NewProductAttribute::class];
    }
    
    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
