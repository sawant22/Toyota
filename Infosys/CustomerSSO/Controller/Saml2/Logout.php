<?php

/**
 * @package   Infosys/CustomerSSO
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright � 2021. All Rights Reserved.
 */

namespace Infosys\CustomerSSO\Controller\Saml2;

use Pitbulk\SAML2\Controller\AbstractCustomController;
use Infosys\CustomerSSO\Helper\Data as SsoHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Pitbulk\SAML2\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Infosys\CustomerSSO\Logger\DCSLogger;

class Logout extends AbstractCustomController
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
     * SSO Logout
     *
     * @return void
     */
    public function execute()
    {
        $helper = $this->_getHelper();
        $storeId = $this->storeManager->getStore()->getId();
        $customerSession = $this->_getCustomerSession();
        $errorMsg = null;

        $moduleEnabled = $helper->checkEnabledModule('frontend');
        if ($moduleEnabled) {
            $sloEnabled = $helper->getConfig('pitbulk_saml2_customer/options/slo');
            if ($sloEnabled) {
                if (
                    $customerSession->isLoggedIn()
                    && $customerSession->getData('saml_login')
                ) {
                    $auth = $this->_getSAMLAuth();
                    $redirectTo = $this->getRequest()->getParam('url');
                    if (!$redirectTo) {
                        $redirectTo =  $this->ssoHelper->getSsoRedirectionUrl($storeId);
                    } else {
                        $getBaseUrl = substr($redirectTo, 0, strpos($redirectTo, ".com"));
                        $redirectTo = $getBaseUrl.'.com';
                    }
                    $this->ssoLogger->info('SSO logout redirect  url'.$redirectTo);
                    $redirectTo = urlencode($redirectTo);
                    $idpSLOBinding = $helper->getConfigIdP('slo_binding');
                    if ($idpSLOBinding == 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST') {
                        $request = $this->getRequest();
                        $response = $this->getResponse();
                        $auth->postLogout(
                            $request,
                            $response,
                            $redirectTo,
                            [],
                            $customerSession->getData('saml_nameid'),
                            $customerSession->getData('saml_sessionindex'),
                            false,
                            $customerSession->getData('saml_nameid_format'),
                            $customerSession->getData('saml_nameid_nq'),
                            $customerSession->getData('saml_nameid_spnq')
                        );
                        return;
                    } else {
                        $auth->logout(
                            $redirectTo,
                            [],
                            $customerSession->getData('saml_nameid'),
                            $customerSession->getData('saml_sessionindex'),
                            false,
                            $customerSession->getData('saml_nameid_format'),
                            $customerSession->getData('saml_nameid_nq'),
                            $customerSession->getData('saml_nameid_spnq')
                        );
                    }
                } else {
                    $errorMsg = "You tried to start a SLO process but you" .
                        " are not logged via SAML";
                }
            } else {
                $errorMsg = "You tried to start a SLO process but this" .
                    " functionality is disabled";
            }
        } else {
            $errorMsg = "You tried to start a SLO process but SAML2 module" .
                " has disabled status";
        }

        if (isset($errorMsg)) {
            $this->processError($errorMsg);
        }
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
        $this->ssoLogger->info('SSO logout redirect error url'.$redirectTo);
        
        return $this->_redirect($redirectTo);
    }
}
