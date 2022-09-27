<?php

namespace Infosys\ProductAttribute\Setup\Patch\Data;

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
            'catalog_id' => [
                'group_name' => 'Vehicle Series',
                'type' => 'int',
                'label' => 'Catalog Id',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'model_year' => [
                'group_name' => 'Vehicle Series',
                'type' => 'int',
                'label' => 'Model Year',
                'input' => 'select',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'model_code' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'Model Code',
                'input' => 'select',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'series_name' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'Series/ Model Name',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'trim' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'Trim',
                'input' => 'select',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'transmission' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'Transmission',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'color_id' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'Vehicle Color',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'wmi' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'WMI',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'production_start_date' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'Production Date',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'production_end_date' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'Production End Date',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'model_year_description' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'Model Year Description',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'model_year_edition' => [
                'group_name' => 'Vehicle Series',
                'type' => 'varchar',
                'label' => 'Model Year Edition',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'katashiki_code' => [
                'group_name' => 'Katashiki',
                'type' => 'int',
                'label' => 'Katashiki Code',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'katashiki_description' => [
                'group_name' => 'Katashiki',
                'type' => 'varchar',
                'label' => 'Katashiki Description',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'category_description' => [
                'group_name' => 'Category',
                'type' => 'varchar',
                'label' => 'Category Description',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'sub_category_description' => [
                'group_name' => 'Sub Category',
                'type' => 'int',
                'label' => 'Sub Category Description',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'marketing_category_description' => [
                'group_name' => 'Marketing Category',
                'type' => 'varchar',
                'label' => 'Marketing Category Description',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'part_name_code' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Part Name Code',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'qty' => [
                'group_name' => 'Part',
                'type' => 'int',
                'label' => 'Part Quantity',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'part_live_date' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Live Date',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'part_expiration_date' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Expiration Date',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'stop_sale_indicator' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Stop Sale Indicator',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'specification_text' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Specification Text',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'part_disclaimer_text' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Part Disclaimer Text',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'part_type' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Part Type',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'detail_category' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Detail Category',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'product_code_description' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Product Code Description',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'major_category' => [
                'group_name' => 'Part',
                'type' => 'varchar',
                'label' => 'Major Category',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'substitution_part_number' => [
                'group_name' => 'Supersession Part',
                'type' => 'varchar',
                'label' => 'Substitution Parts Number',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'substitution_part_name' => [
                'group_name' => 'Supersession Part',
                'type' => 'varchar',
                'label' => 'Substitution Part Name',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'substitution_part_group' => [
                'group_name' => 'Supersession Part',
                'type' => 'varchar',
                'label' => 'Substitution Group',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'marketing_category' => [
                'group_name' => 'Part Marketing',
                'type' => 'varchar',
                'label' => 'Marketing Description',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'customer_text' => [
                'group_name' => 'Part Marketing',
                'type' => 'int',
                'label' => 'Customer Text',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'disclaimer_text' => [
                'group_name' => 'Part Marketing',
                'type' => 'int',
                'label' => 'Disclaimer Text',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'attribute_id' => [
                'group_name' => 'Attributes',
                'type' => 'int',
                'label' => 'Attribute Id',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'attribute_text' => [
                'group_name' => 'Attributes',
                'type' => 'varchar',
                'label' => 'Attribute Text',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'attribute_value' => [
                'group_name' => 'Attributes',
                'type' => 'varchar',
                'label' => 'Attribute Value',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'fob' => [
                'group_name' => 'Amount',
                'type' => 'varchar',
                'label' => 'FOB Amount',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'labor_rate' => [
                'group_name' => 'Amount',
                'type' => 'int',
                'label' => 'Labor Rate',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'labor_time' => [
                'group_name' => 'Amount',
                'type' => 'varchar',
                'label' => 'Labor Time',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'version' => [
                'group_name' => 'Imagery',
                'type' => 'int',
                'label' => 'Version',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'primary_image_indicator' => [
                'group_name' => 'Imagery',
                'type' => 'int',
                'label' => 'Primary Image indicator',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'detail_image' => [
                'group_name' => 'Imagery',
                'type' => 'varchar',
                'label' => 'Detail Image',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => true,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'alt_text' => [
                'group_name' => 'Imagery',
                'type' => 'varchar',
                'label' => 'Alt Text',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'url' => [
                'group_name' => 'Imagery',
                'type' => 'varchar',
                'label' => 'URL',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'voice_search' => [
                'group_name' => 'Imagery',
                'type' => 'varchar',
                'label' => 'Voice Search',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'backend' => ''
            ],
            'live_search' => [
                'group_name' => 'Imagery',
                'type' => 'varchar',
                'label' => 'Live Search',
                'input' => 'text',
                'source' => '',
                'filterable' => false,
                'searchable' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false,
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
                        'default' => '',
                        'searchable' => $data['searchable'],
                        'filterable' => $data['filterable'],
                        'comparable' => false,
                        'visible_on_front' => $data['visible_on_front'],
                        'used_in_product_listing' => $data['used_in_product_listing'],
                        'unique' => false,
                        'apply to' => ''
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
