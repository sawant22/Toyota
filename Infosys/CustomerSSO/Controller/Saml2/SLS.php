<?php

/**
 * @package   Infosys/CustomerSSO
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerSSO\Controller\Saml2;

use Pitbulk\SAML2\Controller\AbstractCustomController;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Pitbulk\SAML2\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Infosys\CustomerSSO\Helper\Data as SsoHelper;
use Infosys\CustomerSSO\Logger\DCSLogger;

class SLS extends AbstractCustomController
{

     /**
     * @var ssoHelper
     */
    protected $ssoHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var ssoLogger
     */
    protected $ssoLogger;
    /**
     * Constructor function
     *
     * @param Context $context
     * @param Session $session
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param FormKey $formKey
     * @param SsoHelper $ssoHelper
     * @param StoreManagerInterface $storeManager
     * @param DCSLogger $ssoLogger
     */
    public function __construct(
        Context $context,
        Session $session,
        Data $helper,
        LoggerInterface $logger,
        FormKey $formKey,
        SsoHelper $ssoHelper,
        StoreManagerInterface $storeManager,
        DCSLogger $ssoLogger
    ) {
        $this->ssoHelper = $ssoHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->ssoLogger = $ssoLogger;
        parent::__construct($context, $session, $helper, $logger, $formKey);
    }
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;
    
    /**
     * SSO login
     *
     * @return void
     */
    public function execute()
    {
        $errorMsg = null;

        $customerSession = $this->_getCustomerSession();

        $helper = $this->_getHelper();
        $request = $this->getRequest();
        $storeId = $this->storeManager->getStore()->getId();
        $redirectTo =  $this->ssoHelper->getSsoRedirectionUrl($storeId);
        $this->ssoLogger->info('SSO sls redirect url'.$redirectTo);
        // Prevent if not logged
        if (!$request->isPost() && !$customerSession->isLoggedIn()) {
            return $this->_redirect($redirectTo);
        }

        $moduleEnabled = $helper->checkEnabledModule('frontend');
        if ($moduleEnabled) {
            $auth = $this->_getSAMLAuth();

            $request = $this->getRequest();
            $response = $this->getResponse();
            $retrieveParametersFromServer = (bool) $helper->getConfig('pitbulk_saml2_customer/advanced/retrieveparametersfromserver');
            $errorInfo = $auth->extendedProcessSLO($request, $response, false, null, $retrieveParametersFromServer, [$this, 'localLogout']);
            if (empty($errorInfo)) {
                $destination =  $request->getParam('RelayState');
                if ($destination) {
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setUrl(urldecode($destination));
                    return $resultRedirect;
                }
                return $this->_redirect($redirectTo);
            } else {
                $errors = $errorInfo["errors"];
                $errorMsg = 'Error at the SLS Endpoint.<br>' .
                    implode(', ', $errors);
                if ($helper->getConfig('pitbulk_saml2_customer/advanced/debug')) {
                    $reason = $errorInfo["reason"];
                    if (isset($reason) && !empty($reason)) {
                        $errorMsg .= '<br><br>Reason: ' . $reason;
                    }
                }
            }
        } else {
            $errorMsg = 'SAML module has disabled status';
        }

        if (isset($errorMsg)) {
            $this->processError($errorMsg);
        }

        if ($request->isPost() && !$customerSession->isLoggedIn()) {
            return $this->_redirect($redirectTo);
        }
    }

    public function localLogout()
    {
        $customerSession = $this->_getCustomerSession();
        $customerSession->unsetData('saml_login');
        $customerSession->logout();

        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Process Error
     *
     * @param string $errorMsg
     * @param string $extraInfo
     * @return void
     */
    public function processError($errorMsg, $extraInfo = null)
    {
        $this->ssoLogger->error($errorMsg);
        if (isset($extraInfo)) {
            $this->ssoLogger->error($extraInfo);
        }
        $storeId = $this->storeManager->getStore()->getId();
        $redirectTo =  $this->ssoHelper->getSsoRedirectionUrl($storeId);
        $this->ssoLogger->info('SSO sls redirect error url'.$redirectTo);
        return $this->_redirect($redirectTo);
    }
}
