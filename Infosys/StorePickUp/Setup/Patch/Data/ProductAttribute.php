<?php
/**
 * @package     Infosys/StorePickUp 
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\StorePickUp\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Catalog\Api\AttributeSetManagementInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;

class ProductAttribute implements DataPatchInterface
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
     * Constructor function
     *
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
        $attributes = [
			'in_store_pick_up_availability' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'In Store Pick Up Availability',
                'input' => 'select',
                'source' => 'Infosys\StorePickUp\Model\Config\Source\Options',
				'visible' => true,
                'filterable' => true,
                'searchable' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
				'visible_in_advanced_search' => true,
				'is_html_allowed_on_front' => false,
                'backend' => ''
            ],
        ];
        $this->createProductAttribute($attributes);
        $this->moduleDataSetup->endSetup();
    }
    /**
     * Method to create Product Attributes
     *
     * @param array $attributes
     * @return void
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
                        // Let empty, if we want to set an attribute group id
                        'group' => $attributeGroupId ? '' : 'General',
                        'type' => $data['type'],
                        'backend' => $data['backend'],
                        'frontend' => '',
                        'label' => $data['label'],
                        'input' => $data['input'],
                        'class' => '',
                        'source' => $data['source'],
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'default' => 'no',
                        'searchable' => $data['searchable'],
                        'filterable' => $data['filterable'],
                        'comparable' => false,
                        'visible_on_front' => $data['visible_on_front'],
                        'used_in_product_listing' => $data['used_in_product_listing'],
                        'unique' => false,
                        'apply_to' => 'simple,grouped,configurable,downloadable,virtual,bundle'
                    ]
                );
                if ($attributeGroupId) {
                    /**
                     * Set the attribute in the right attribute group in the right attribute set
                     */
                    $eavSetup->addAttributeToGroup($productEntity, $attributeSetId, $attributeGroupId, $attribute);
                }
            }
        }
    }
    /**
     * Method to create Attribute Group
     *
     * @param string $attributeGroupName
     * @param int $attributeSetId
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
     * Dependencies function
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
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
