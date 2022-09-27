<?php

/**
 * @package     Infosys/Vehicle
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare (strict_types = 1);

namespace Infosys\Vehicle\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * Data patch to create attribute sets
 */
class CreateAttributeSets implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
    
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AttributeSetFactory $attributeSetFactory
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ModuleDataSetupInterface $moduleDataSetup,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        try {
            $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
            $attributeSet = $this->attributeSetFactory->create();
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
    
            //Create custom attribute sets
            $sets = ['Service Part', 'Accessory'];
            foreach ($sets as $set) {
    
                $data = [
                    'attribute_set_name' => $set,
                    'entity_type_id' => $entityTypeId,
                    'sort_order' => 200,
                ];
                $attributeSet->setData($data);
                $attributeSet->validate();
                $attributeSet->save();
                $attributeSet->initFromSkeleton($attributeSetId);
                $attributeSet->save();
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
