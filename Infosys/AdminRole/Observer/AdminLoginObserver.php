<?php

/**
 * @package     Infosys/AdminRole
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\AdminRole\Observer;

use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\User\Model\UserFactory;
use Magento\Security\Model\AdminSessionsManager;
use OneLogin\Saml2\Utils;
use Infosys\AdminRole\Helper\Data as WebsiteHelper;


use Pitbulk\SAML2\Helper\Data;

/**
 * Overriding the class to include website id mapping during SSO admin login
 */
class AdminLoginObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var UserFactory
     */
    private $userFactory;
    /**
     * @var MessageManager
     */
    private $messageManager;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var AdminSessionsManager
     */
    private $adminSessionsManager;
    /**
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @var Random
     */
    private $mathRandom;
    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;
    /**
     * @var WebsiteHelper
     */
    private $websiteHelper;
    /**
     * Undocumented function
     *
     * @param Data $helper
     * @param Session $session
     * @param RequestInterface $request
     * @param UserFactory $userFactory
     * @param MessageManager $messageManager
     * @param Registry $registry
     * @param EventManager $eventManager
     * @param AdminSessionsManager $adminSessionsManager
     * @param DateTime $dateTime
     * @param Random $mathRandom
     * @param EncryptorInterface $encryptor
     * @param RoleFactory $roleFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param WebsiteHelper $websiteHelper
     */
    public function __construct(
        Data $helper,
        Session $session,
        RequestInterface $request,
        UserFactory $userFactory,
        MessageManager $messageManager,
        Registry $registry,
        EventManager $eventManager,
        AdminSessionsManager $adminSessionsManager,
        DateTime $dateTime,
        Random $mathRandom,
        EncryptorInterface $encryptor,
        RoleFactory $roleFactory,
        RedirectFactory $resultRedirectFactory,
        WebsiteHelper $websiteHelper
    ) {
        $this->helper = $helper;
        $this->session = $session;
        $this->request = $request;
        $this->userFactory = $userFactory;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->eventManager = $eventManager;
        $this->adminSessionsManager = $adminSessionsManager;
        $this->dateTime = $dateTime;
        $this->mathRandom = $mathRandom;
        $this->encryptor = $encryptor;
        $this->roleFactory = $roleFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->websiteHelper = $websiteHelper;
    }

    public function execute(Observer $observer)
    {
        if (
            $this->request->getActionName() !== "login"
            || strpos($this->request->getPathInfo(), 'sso/saml2/sls') !== false
        ) {
            return;
        }

        if (
            null === $this->request->getParam("SAMLResponse") ||
            $this->registry->registry('admin_login_observer_fired')
        ) {
            return;
        }

        // Prevent if already logged
        if ($this->session->isLoggedIn()) {
            return;
        }

        $this->registry->register('admin_login_observer_fired', true);

        try {
            $moduleEnabled = $this->helper->checkEnabledModule('backend');
            if (!$moduleEnabled) {
                throw new ConfigurationMismatchException(__('SAML Backend module has disabled status'));
            }

            $auth = $this->helper->getAuth('backend');
            $auth->processResponse();
            $errors = $auth->getErrors();
            if (!empty($errors)) {
                $errorMsg = "Error at the Backend ACS Endpoint.<br>" . implode(', ', $errors);
                $debug = $this->helper->getConfig('pitbulk_saml2_admin/advanced/debug');
                $reason = $auth->getLastErrorReason();
                if ($debug && isset($reason) && !empty($reason)) {
                    $errorMsg .= '<br><br>Reason: ' . $reason;
                }
                throw new AuthenticationException(__($errorMsg));
            } elseif (!$auth->isAuthenticated()) {
                throw new AuthenticationException(__("Backend ACS Process failed"));
            }

            $this->handleValidSamlResponse($auth);
        } catch (AuthenticationException $e) {
            $this->_processError($e->getMessage());
        } catch (\Exception $e) {
            $this->_processError($e->getMessage());
        }
    }

    private function handleValidSamlResponse($auth)
    {
        $userData = $this->processAttrs($auth);

        $user = $this->tryGetUser($userData);

        if (!empty($user) && $user->getId()) {
            if ($user->getIsActive() != '1') {
                throw new AuthenticationException(
                    __('You did not sign in correctly or your account is temporarily disabled.')
                );
            }
            $user = $this->updateUser($user, $userData);

            if (!$user->hasAssigned2Role($user->getId())) {
                throw new AuthenticationException(__('You need more permissions to access this.'));
            }
        } else {
            $user = $this->provisionUser($userData);
            //$baseUrl = $this->helper->getBaseStoreUrl()."sso/saml2/backendlogin";
            //Utils::redirect($baseUrl);
        }

        if (!empty($user)) {
            $this->registerUserSession($auth, $user);
        }
    }

    /**
     * User finder
     *
     */
    private function tryGetUser($userData)
    {
        $user = null;
        $useridfield = $this->helper->getConfig('pitbulk_saml2_admin/options/useridfield');
        if (empty($useridfield)) {
            $useridfield = 'email';
        }

        if (!isset($userData[$useridfield])) {
            $errorMsg = "The field to identify the user is the '" . $useridfield . "' but IdP is not providing it";
            throw new ConfigurationMismatchException(__($errorMsg));
        }

        $user = $this->userFactory->create()->load($userData[$useridfield], $useridfield);

        return $user;
    }

    /**
     * Provision user
     *
     */
    private function provisionUser($userData)
    {
        $autocreate = $this->helper->getConfig('pitbulk_saml2_admin/options/autocreate');
        if ($autocreate) {
            try {
                $user = $this->userFactory->create();

                $user->setUserName($userData['username']);
                $user->setEmail($userData['email']);
                $user->setFirstname($userData['firstname']);
                $user->setLastname($userData['lastname']);
                $user->setAllWebsite($userData['all_website']);
                $user->setWebsiteIds($userData['website_ids']);

                $randomPw = $this->mathRandom->getRandomString(
                    2,
                    \Magento\Framework\Math\Random::CHARS_LOWERS
                );
                $randomPw .= $this->mathRandom->getRandomString(
                    2,
                    \Magento\Framework\Math\Random::CHARS_UPPERS
                );
                $randomPw .= $this->mathRandom->getRandomString(
                    2,
                    \Magento\Framework\Math\Random::CHARS_DIGITS
                );
                $randomPw .= $this->mathRandom->getRandomString(6);
                $user->setIsActive(1);
                $user->setPassword($randomPw);

                if (empty($userData['roleid'])) {
                    $requireRoleForJit = $this->helper->getConfig('pitbulk_saml2_admin/options/requireroleforjit');
                    if ($requireRoleForJit) {
                        $errorMsg = 'The login could not be completed, user ' . $userData['email'] .
                            ' does not exist in Magento and the auto-provisioning' .
                            " function can't be executed because role info was not " .
                            "provided and the extension is configured to require it";
                        throw new AuthenticationException(__($errorMsg));
                    }

                    $defaultRole = $this->helper->getConfig('pitbulk_saml2_admin/options/defaultrole');
                    if (empty($defaultRole)) {
                        $defaultRole = 1;
                    }
                    $userData['roleid'] = $defaultRole;
                }

                if (!empty($userData['roleid'])) {
                    $role = $this->roleFactory->create()->load($userData['roleid']);
                    if (!isset($role) || empty($role->getData()) || $role->getRoleType() != RoleGroup::ROLE_TYPE) {
                        $errorMsg = 'The login could not be completed, user ' . $userData['email'] .
                            ' does not exist in Magento and the auto-provisioning' .
                            " function can't be executed because role info " .
                            "provided was wrong, contact the administrator and ask for review role mapping settings";
                        throw new AuthenticationException(__($errorMsg));
                    } else {
                        $user->setRoleId($userData['roleid']);
                    }
                }

                $user->save();
                $successMsg = __('User registration successful.');
                $this->messageManager->addSuccess($successMsg);
                $user = $this->userFactory->create()->load($user->getId());
                return $user;
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('The auto-provisioning process failed: ' . $e->getMessage()));
            }
        } else {
            $errorMsg = 'The login could not be completed, user ' . $userData['email'] .
                ' does not exist in Magento and the auto-provisioning' .
                ' function is disabled';
            throw new AuthenticationException(__($errorMsg));
        }
    }

    /**
     * Update user
     *
     */
    private function updateUser($user, $userData)
    {
        $updateuser = $this->helper->getConfig('pitbulk_saml2_admin/options/updateuser');
        if ($updateuser) {
            if (!empty($userData['firstname'])) {
                $user->setFirstname($userData['firstname']);
            }
            if (!empty($userData['lastname'])) {
                $user->setLastname($userData['lastname']);
            }
            if (!empty($userData['all_website'])) {
                $user->setAllWebsite($userData['all_website']);
            }
            if (!empty($userData['website_ids'])) {
                $user->setWebsiteIds($userData['website_ids']);
            }
            if (!empty($userData['roleid'])) {
                $role = $this->roleFactory->create()->load($userData['roleid']);
                if (isset($role) && !empty($role->getData()) && $role->getRoleType() == RoleGroup::ROLE_TYPE) {
                    $user->setRoleId($userData['roleid']);
                }
            }
            $user->save();
        }
        return $user;
    }

    /**
     * Register user session
     *
     */
    private function registerUserSession($auth, $user)
    {
        $this->session->setUser($user);
        $this->adminSessionsManager->getCurrentSession()->load($this->session->getSessionId());
        $sessionInfo = $this->adminSessionsManager->getCurrentSession();
        $sessionInfo->setUpdatedAt($this->dateTime->gmtTimestamp());
        $sessionInfo->setStatus($sessionInfo::LOGGED_IN);
        $sessionInfo->save();
        $this->adminSessionsManager->processLogin();

        $nameId = $auth->getNameId();
        $nameIdFormat = $auth->getNameIdFormat();
        $sessionIndex = $auth->getSessionIndex();
        $nameIdNameQualifier = $auth->getNameIdNameQualifier();
        $nameIdSPNameQualifier = $auth->getNameIdSPNameQualifier();

        $user->setSamlLogin(true);
        $user->setSamlNameId($nameId);
        $user->setSamlNameidFormat($nameIdFormat);
        $user->setSamlSessionIndex($sessionIndex);
        $user->setSamlNameIdNameQualifier($nameIdNameQualifier);
        $user->setSamlNameIdSPNameQualifier($nameIdSPNameQualifier);

        $this->eventManager->dispatch(
            'backend_auth_user_login_success',
            ['user' => $user]
        );
    }

    /**
     * Process SAML Attributes
     *
     */
    private function processAttrs($auth)
    {
        $userData = [
            'username' => '',
            'email' => '',
            'firstname' => '',
            'lastname' => '',
            'roleid' => '',
            'all_website' => '',
            'website_ids' => '',
            'region_code' => '',
            'corporate_region_code' => ''
        ];
        $userMapData = [
            'username',
            'email',
            'firstname',
            'lastname',
            'website_ids',
            'region_code',
            'corporate_region_code'
        ];

        $attrs = $auth->getAttributes();
        if (empty($attrs)) {
            $userData['username'] = $auth->getNameId();
            $userData['email'] = $auth->getNameId();
        } else {
            $mapping = $this->getAttrMapping();
            foreach ($userMapData as $key) {
                if (!empty($mapping[$key])) {
                    $mapped_keys = explode(",", trim($mapping[$key]));
                    foreach ($mapped_keys as $mapped_key) {
                        if (isset($attrs[$mapped_key]) && !empty($attrs[$mapped_key][0])) {
                            $userData[$key] = trim($attrs[$mapping[$key]][0]);
                        }
                    }
                }
            }

            if (!empty($mapping['role'])) {
                $mapped_keys = explode(",", $mapping['role']);
                $roleValues = [];
                foreach ($mapped_keys as $mapped_key) {
                    if (isset($attrs[$mapped_key]) && !empty($attrs[$mapped_key])) {
                        $roleValues = array_merge($roleValues, $attrs[$mapped_key]);
                    }
                }
                $roleValues = array_unique($roleValues);
                if (!empty($roleValues)) {
                    $roleValues =  explode(",", current($roleValues));
                    foreach ($roleValues as $roleValue) {
                        $roleid = $this->calculateRoleId(explode(",", $roleValue));
                        if ($roleid !== false) {
                            $userData['roleid'] = $roleid;
                            break;
                        }
                    }
                }
            }
            // mapping website id from sso respose
            $dealerWebsites = '';
            if ($userData['corporate_region_code']) {
                #remove trailing 0 from corporate region code to get dealer region code
                $regionCode = substr_replace($userData['corporate_region_code'], "", -1);
                $dealerWebsites = $this->websiteHelper->getRegionalWebsite($regionCode);
            } elseif ($userData['website_ids']) {
                $dealerWebsites = $this->websiteHelper->getDealerWebsite($userData['website_ids']);
            }
            if ($dealerWebsites) {
                $userData['all_website'] = 0;
                $userData['website_ids'] = $dealerWebsites;
            } else {
                if (in_array($mapping['admin_role_code'], $roleValues)) {
                    $userData['all_website'] = 1;
                } else {
                    $errorMsg = 'The login could not be completed, user website does not exist.';
                    throw new AuthenticationException(__($errorMsg));
                }
            }
        }
        return $userData;
    }

    private function calculateRoleId($roleValues)
    {
        $roleMappingMode = $this->helper->getConfig('pitbulk_saml2_admin/options/rolemappingmode');
        if (empty($roleMappingMode) || $roleMappingMode == "mapping_mode") {
            $roleMapping = $this->getRoleMapping();
            $roleid = $this->obtainRoleId($roleValues, $roleMapping);
        } else {
            $roleid = false;
            if ($roleMappingMode == "name_mode") {
                $availableRoles = $this->helper->getAvailableRoles('name');
                foreach ($roleValues as $roleValue) {
                    if (isset($availableRoles[$roleValue])) {
                        $roleid = $availableRoles[$roleValue];
                        break;
                    }
                }
            } else {
                $availableRoles = $this->helper->getAvailableRoles('id');
                foreach ($roleValues as $roleValue) {
                    if (isset($availableRoles[$roleValue])) {
                        $roleid = $roleValue;
                        break;
                    }
                }
            }
        }
        return $roleid;
    }

    /**
     * Aux method for get role mapping
     *
     */
    private function getRoleMapping()
    {
        $roleMapping = [];
        for ($i = 1; $i < 401; $i++) {
            $key = 'pitbulk_saml2_admin/role_mapping/role' . $i;
            $maps = $this->helper->getConfig($key);
            $roleMapping[$i] = explode(',', trim($maps));
        }

        return $roleMapping;
    }

    /**
     * Aux method for get attribute mapping
     *
     */
    private function getAttrMapping()
    {
        $mapping = [];

        $attrMapKey = 'pitbulk_saml2_admin/attr_mapping/';

        $mapping['username'] =  $this->helper->getConfig($attrMapKey . 'username');
        $mapping['email'] =  $this->helper->getConfig($attrMapKey . 'email');
        $mapping['firstname'] =  $this->helper->getConfig($attrMapKey . 'firstname');
        $mapping['lastname'] =  $this->helper->getConfig($attrMapKey . 'lastname');
        $mapping['role'] = $this->helper->getConfig($attrMapKey . 'role');
        $mapping['website_ids'] = $this->helper->getConfig($attrMapKey . 'website_ids');
        $mapping['region_code'] = $this->helper->getConfig($attrMapKey . 'region_code');
        $mapping['corporate_region_code'] = $this->helper->getConfig($attrMapKey . 'corporate_region_code');
        $mapping['admin_role_code'] = $this->helper->getConfig($attrMapKey . 'admin_role_code');

        return $mapping;
    }

    /**
     * Aux method for calculating roleid
     *
     */
    private function obtainRoleId($samlRoles, $roleValues)
    {
        foreach ($samlRoles as $samlRole) {
            foreach ($roleValues as $i => $values) {
                if (in_array($samlRole, $values)) {
                    return $i;
                }
            }
        }
        return false;
    }

    private function _processError($errorMsg)
    {
        $this->messageManager->addErrorMessage($errorMsg);
    }
}
