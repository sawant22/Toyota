<?php

/**
 * @package     Infosys/Vehicle
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Vehicle\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Product attribute data patch
 */
class UpdateWhatThisFitsAttribute implements DataPatchInterface
{
    private EavSetupFactory $eavSetupFactory;

    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * Constructor function
     *
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
     * Patch to update what this fits attribute
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityType = $eavSetup->getEntityTypeId('catalog_product');
        $eavSetup->updateAttribute($entityType, 'what_this_fits', 'is_global', Attribute::SCOPE_GLOBAL, null);
        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [WhatThisFitsAttribute::class];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
