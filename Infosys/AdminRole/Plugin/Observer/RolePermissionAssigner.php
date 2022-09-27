<?php
/**
 * @package     Infosys/AdminRole
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\AdminRole\Plugin\Observer;

use Magento\Backend\Model\Auth\Session as BackendSession;

class RolePermissionAssigner
{
    /**
     * @var BackendSession
     */
    protected $backendSession;
    /**
     * constructor function
     *
     * @param BackendSession $backendSession
     */
    public function __construct(BackendSession $backendSession)
    {
        $this->backendSession = $backendSession;
    }
    /**
     * Overiding the metod to change website permission from role to user
     *
     * @param \Magento\AdminGws\Observer\RolePermissionAssigner $subject
     * @param \Magento\Authorization\Model\Role $object
     * @return void
     */
    public function beforeAssignRolePermissions(
        \Magento\AdminGws\Observer\RolePermissionAssigner $subject,
        \Magento\Authorization\Model\Role $object
    ) {
        $userdata = $this->backendSession->getUser();
        if ($userdata && !$userdata->getAllWebsite()) {
            $object->setGwsIsAll(0);
            $object->setGwsWebsites($userdata->getWebsiteIds());
			$object->setGwsStoreGroups('');
            return [$object];
        }

        return [$object];
    }
}
