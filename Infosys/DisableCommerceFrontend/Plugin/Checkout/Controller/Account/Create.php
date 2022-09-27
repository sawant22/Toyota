<?php
/**
 * @package     Infosys/DisableCommerceFrontend 
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DisableCommerceFrontend\Plugin\Checkout\Controller\Account;

use Infosys\DisableCommerceFrontend\Helper\Data;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * checkout create class
 * 
 */
class Create{
    /**
     * @var Helper
     */
    protected Data $helper;

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $resultRedirectFactory;


    /**
     * Construct function
     *
     * @param Data $helper
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Data $helper,
        RedirectFactory $resultRedirectFactory
    )
    {
        $this->helper = $helper;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Overriding the method to redirect AEM frontend when commerece frontend is disabled.
     *
     * @param Create $subject
     * @param Closure $proceed
     */
    public function aroundExecute(\Magento\Checkout\Controller\Account\Create $subject,\Closure $proceed)
    {
        // call the core observed function
        $resultRedirect = $proceed();
        if($this->helper->isFrontendEnabled()){
            $resultRedirect = $this->resultRedirectFactory->create();
            $redirectURL = $this->helper->getRedirectionUrl();
            $resultRedirect->setUrl($redirectURL);
        }
        return $resultRedirect;
    }
} 
