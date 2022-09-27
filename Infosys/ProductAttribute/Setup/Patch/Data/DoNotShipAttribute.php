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
use Magento\Eav\Api\AttributeManagementInterface;

/**
 * Product attribute data patch
 */
class DoNotShipAttribute implements DataPatchInterface
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
     * @var AttributeManagementInterface
     */
    private $attributeManagementInterface;
    
    /**
     * Constuctor function
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AttributeSetFactory $attributeSetFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param Product $product
     * @param AttributeSetManagementInterface $attributeSetManagement
     * @param AttributeGroupInterfaceFactory $attributeGroupFactory
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     * @param AttributeManagementInterface $attributeManagementInterface
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        AttributeSetFactory $attributeSetFactory,
        EavSetupFactory $eavSetupFactory,
        Product $product,
        AttributeSetManagementInterface $attributeSetManagement,
        AttributeGroupInterfaceFactory $attributeGroupFactory,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        AttributeManagementInterface $attributeManagementInterface
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->product = $product;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeManagementInterface = $attributeManagementInterface;
    }
    /**
     * Patch to create Product Attributes
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $attributes = [
            'do_not_ship ' => [
                'group_name' => 'PDP Custom Attributes',
                'type' => 'varchar',
                'label' => 'Do Not Ship',
                'input' => 'text',
                'source' => '',
                'option' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ]
        ];
        
        $this->createProductAttribute($attributes);
        $this->moduleDataSetup->endSetup();
    }
    
    /**
     * Method to create Product Attributes
     *
     * @param array $attributes
     */
    private function createProductAttribute($attributes)
    {
        $eavSetup = $this->eavSetupFactory->create();
        $productEntity = \Magento\Catalog\Model\Product::ENTITY;
        $attributeSetId = $this->product->getDefaultAttributeSetId();
        
        foreach ($attributes as $attribute => $data) {
            $eavSetup = $this->eavSetupFactory->create();
            
            $attributeGroupName = $data['group_name'];
            /**
             * creating Attribute Groups
             */
            if (!$eavSetup->getAttributeGroup($productEntity, $attributeSetId, $attributeGroupName)) {
                $this->createAttributeGroup($attributeGroupName, $attributeSetId);
            }
            $attributeGroupId = $eavSetup->getAttributeGroupId($productEntity, $attributeSetId, $attributeGroupName);
            /**
             * Add attributes to the eav/attribute
             */
            if (!$eavSetup->getAttributeId($productEntity, $attribute)) {
                $eavSetup->addAttribute(
                    $productEntity,
                    $attribute,
                    [
                        'group' => $attributeGroupId ? '' : 'General',
                        // Let empty, if we want to set an attribute group id
                        'type' => $data['type'],
                        'backend' => $data['backend'],
                        'frontend' => '',
                        'label' => $data['label'],
                        'input' => $data['input'],
                        'class' => '',
                        'source' => $data['source'],
                        'option' => $data['option'],
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'default' => '',
                        'searchable' => $data['searchable'],
                        'filterable' => $data['filterable'],
                        'comparable' => false,
                        'visible_on_front' => $data['visible_on_front'],
                        'used_in_product_listing' => $data['used_in_product_listing'],
                        'unique' => false
                    ]
                );
                if ($attributeGroupId!==null) {
                    /**
                    * Set the attribute in the right attribute group in the right attribute set
                    */
                    $eavSetup->addAttributeToGroup($productEntity, $attributeSetId, $attributeGroupId, $attribute);
                }

                $this->assignAttributeToSets($attribute, $attributeGroupName, $productEntity);
            }
        }
    }

    /**
     * Method to add attribute in multiple sets
     *
     * @param string $attribute_code
     * @param string $attribute_group
     * @param string $productEntity
     * @return void
     */
    public function assignAttributeToSets($attribute_code, $attribute_group, $productEntity)
    {
        $eavSetup = $this->eavSetupFactory->create();
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($productEntity);
    
        foreach ($attributeSetIds as $attributeSetId) {
            $group_id = $eavSetup->getAttributeGroupId($productEntity, $attributeSetId, $attribute_group);
            if ($attributeSetId) {
                $this->attributeManagementInterface->assign(
                    'catalog_product',
                    $attributeSetId,
                    $group_id,
                    $attribute_code,
                    999
                );
            }
        }
    }

    /**
     * Method to create Attribute Group
     *
     * @param string $attributeGroupName
     * @param string $attributeSetId
     * @return void
     */
    private function createAttributeGroup($attributeGroupName, $attributeSetId)
    {
        $attributeGroup = $this->attributeGroupFactory->create();
        $attributeGroup->setAttributeSetId($attributeSetId);
        $attributeGroup->setAttributeGroupName($attributeGroupName);
        $this->attributeGroupRepository->save($attributeGroup);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [ProductAttribute::class];
    }
    
    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
