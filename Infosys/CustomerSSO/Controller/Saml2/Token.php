<?php

/**
 * @package   Infosys/CustomerSSO
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerSSO\Controller\Saml2;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Data\Form\FormKey;
use Pitbulk\SAML2\Controller\AbstractCustomController;
use Pitbulk\SAML2\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;

class Token extends AbstractCustomController
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TokenModelFactory
     */
    protected $tokenFactory;

    /**
     * Constructor function
     *
     * @param Context $context
     * @param Session $session
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param FormKey $formKey
     * @param JsonFactory $resultJsonFactory
     * @param TokenModelFactory $tokenFactory
     */
    public function __construct(
        Context $context,
        Session $session,
        Data $helper,
        LoggerInterface $logger,
        FormKey $formKey,
        JsonFactory $resultJsonFactory,
        TokenModelFactory $tokenFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tokenFactory = $tokenFactory;
        parent::__construct($context, $session, $helper, $logger, $formKey);
    }
    /**
     * SSO login
     *
     * @return Json
     */
    public function execute(): Json
    {
        $helper = $this->_getHelper();
        $customerSession = $this->_getCustomerSession();
        $errorMsg = null;
        $moduleEnabled = $helper->checkEnabledModule();
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([ 'customerToken' => '' ]);
        if ($moduleEnabled) {
            if ($customerSession->isLoggedIn()) {
                $token = $this->tokenFactory->create()->createCustomerToken($customerSession->getId())->getToken();
                $resultJson->setData([ 'customerToken' => $token ]);
            }
        } else {
            $errorMsg = "You tried to start a SSO process but" .
                " SAML2 module has disabled status";
        }

        if (isset($errorMsg)) {
            $this->_processError($errorMsg);
        }
        return $resultJson;
    }
}