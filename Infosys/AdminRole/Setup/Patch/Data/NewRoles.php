<?php

/**
 * @package     Infosys/AdminRole
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\AdminRole\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

class NewRoles implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var RoleFactory
     */
    private $roleFactory;
    /**
     * @var RulesFactory
     */
    private $rulesFactory;
    /**

     * @var AclRetriever
     */
    private $acl;
    /**
     * Constructor function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param RoleFactory $roleFactory
     * @param RulesFactory $rulesFactory
     * @param AclRetriever $_acl
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RoleFactory $roleFactory,
        RulesFactory $rulesFactory,
        AclRetriever $_acl
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->roleFactory = $roleFactory;
        $this->rulesFactory = $rulesFactory;
        $this->acl = $_acl;
    }
    /**
     * Patch to create user roles and permissions
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $role = $this->roleFactory->create();
        $collection = $role->getCollection();
        foreach ($collection as $avroles) {
            $availableRoles[] = $avroles->getRoleName();
        }
        $newRoles = [
            'PCO eCommerce Dealer Order Processing' => ['Magento_Backend::all'],
            'PCO eCommerce Dealer Program Management' => ['Magento_Backend::all'],
            'PCO eCommerce Corporate' => ['Magento_Backend::all'],
        ];
        $keys = array_keys($newRoles);
        foreach ($keys as $newrole) {
            if (!(in_array($newrole, $availableRoles))) {
                $role = $this->roleFactory->create();
                $role->setName($newrole)
                    ->setPid(0)
                    ->setRoleType(RoleGroup::ROLE_TYPE)
                    ->setUserType(UserContextInterface::USER_TYPE_ADMIN);
                $role->save();

                $resources = $newRoles[$newrole];

                $this->rulesFactory->create()
                    ->setRoleId($role->getId())
                    ->setResources($resources)
                    ->saveRel();
            } else {
                foreach ($collection as $var) {
                    if ($var->getRoleName() == $newrole) {
                        $roleId = $var->getId();
                        break;
                    }
                }
                $newResources = $newRoles[$newrole];
                $oldResources = $this->acl->getAllowedResourcesByRole($roleId);
                $resources = array_merge($oldResources, $newResources);
                $this->rulesFactory->create()
                    ->setRoleId($roleId)
                    ->setResources($resources)
                    ->saveRel();
            }
        }
        $this->moduleDataSetup->endSetup();
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
    /**
     * Dependencies function
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
