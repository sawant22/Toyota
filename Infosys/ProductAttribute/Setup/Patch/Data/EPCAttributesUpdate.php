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
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Catalog\Api\AttributeSetManagementInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;

/**
 * Product attribute data patch
 */
class EPCAttributesUpdate implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var AttributeGroupInterfaceFactory
     */
    private $attributeGroupFactory;

    /**
     * @var AttributeSetManagementInterface
     */
    private $attributeSetManagement;

    /**
     * @var AttributeGroupRepositoryInterface
     */
    private $attributeGroupRepository;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    
    /**
     * Constuctor function
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AttributeSetFactory $attributeSetFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param Product $product
     * @param AttributeSetManagementInterface $attributeSetManagement
     * @param AttributeGroupInterfaceFactory $attributeGroupFactory
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        AttributeSetFactory $attributeSetFactory,
        EavSetupFactory $eavSetupFactory,
        Product $product,
        AttributeSetManagementInterface $attributeSetManagement,
        AttributeGroupInterfaceFactory $attributeGroupFactory,
        AttributeGroupRepositoryInterface $attributeGroupRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->product = $product;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeGroupRepository = $attributeGroupRepository;
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

        $eavSetup->updateAttribute($entityType, 'substitution_part_number', 'frontend_input', 'textarea', null);
        $eavSetup->updateAttribute($entityType, 'substitution_part_number', 'backend_type', 'text', null);
        $eavSetup->updateAttribute($entityType, 'attribute_value', 'frontend_input', 'textarea', null);
        $eavSetup->updateAttribute($entityType, 'attribute_value', 'backend_type', 'text', null);

        $eavSetup->updateAttribute($entityType, 'substitution_part_number', 'is_wysiwyg_enabled', 1, null);
        $eavSetup->updateAttribute($entityType, 'attribute_value', 'is_wysiwyg_enabled', 1, null);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [NewEPCProductAttribute::class];
    }
    
    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
