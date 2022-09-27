<?php

/**
 * @package     Infosys/Vehicle
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare (strict_types = 1);

namespace Infosys\Vehicle\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Data patch to remove vehicle attributes
 */
class RemoveVehicleAttributes implements DataPatchInterface
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
            //Remove vehicle attributes
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $attributes = ['brand', 'model_year', 'model_code', 'series_name', 'grade', 'driveline', 'body_style',
                'engine_type', 'model_range', 'model_description', 'transmission', 'marketing_nm'];
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
