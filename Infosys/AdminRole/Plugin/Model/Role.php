<?php
/**
 * @package     Infosys/AdminRole
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\AdminRole\Plugin\Model;

use Magento\Backend\Model\Auth\Session as BackendSession;

class Role
{
    /**
     * @var BackendSession
     */
    protected $backendSession;
    /**
     * constuctor function
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
     * @param \Magento\AdminGws\Model\Role $subject
     * @param  $result
     * @return void
     */
    public function afterGetIsAll(\Magento\AdminGws\Model\Role $subject, $result)
    {
        $userdata = $this->backendSession->getUser();
        if ($userdata && $userdata->getAllWebsite()) {
            return $userdata->getAllWebsite();
        }
        return $result;
    }
    /**
     * Overiding the metod to change website permission from role to user
     *
     * @param \Magento\AdminGws\Model\Role $subject
     * @param  $result
     * @return void
     */
    public function afterGetWebsiteIds(\Magento\AdminGws\Model\Role $subject, $result)
    {
        $userdata = $this->backendSession->getUser();
        if ($userdata && $userdata->getWebsiteIds()) {
            return explode(',', $userdata->getWebsiteIds());
        }
        return $result;
    }
    /**
     * Overiding the metod to change website permission from role to user
     *
     * @param \Magento\AdminGws\Model\Role $subject
     * @param  $result
     * @return void
     */
    public function afterGetRelevantWebsiteIds(\Magento\AdminGws\Model\Role $subject, $result)
    {
        $userdata = $this->backendSession->getUser();
        if ($userdata && $userdata->getWebsiteIds()) {
            return explode(',', $userdata->getWebsiteIds());
        }
        return $result;
    }
}
