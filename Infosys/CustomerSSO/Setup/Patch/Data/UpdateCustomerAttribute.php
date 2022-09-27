<?php

/**
 * @package   Infosys/CustomerSSO
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\CustomerSSO\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

class UpdateCustomerAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;

    /**
     * Constructor function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }
    /**
     * Update customer attribute to show in admin customer form
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->updateAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'dcs_guid',
            [
                'is_user_defined' => 1,
                'sort_order' => 20
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'dcs_guid'
        );
        $attribute->setData(
            'used_in_forms',
            [
                'adminhtml_customer'
            ]
        );

        $attribute->getResource()->save($attribute);
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get Aliases
     *
     * @return void
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return void
     */
    public static function getDependencies()
    {
        return [AddCustomerAttribute::class];
    }
}
