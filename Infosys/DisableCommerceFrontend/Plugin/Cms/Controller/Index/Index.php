<?php

/**
 * @package     Infosys/DisableCommerceFrontend
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DisableCommerceFrontend\Plugin\Cms\Controller\Index;

use Infosys\DisableCommerceFrontend\Helper\Data;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Cms\Controller\Index\Index as CmsIndex;

/**
 * Redirect Commerce Homepage to AEM
 */
class Index
{

    /**
     * @var Helper
     */
    protected Data $helper;

    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $_response;

    protected RedirectFactory $redirectFactory;

    /**
     * Construct function
     *
     * @param Data $helper
     * @param ResponseInterface $response
     *
     */
    public function __construct(
        Data $helper,
        ResponseInterface $response,
        RedirectFactory $redirectFactory
    ) {
        $this->helper = $helper;
        $this->_response = $response;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Overriding the method to redirect AEM frontend when commerece frontend is disabled.
     *
     * @param Index $subject
     * @param Closure $proceed
     */
    public function aroundExecute(CmsIndex $subject, \Closure $proceed)
    {
        // call the core observed function
        $resultRedirect = $proceed();
        if (
            $this->helper->isFrontendEnabled() &&
            $this->helper->isHomePageEnabled()
        ) {
            $redirectURL = $this->helper->getRedirectionUrl();
            $resultRedirect = $this->_response->setRedirect($redirectURL, 301)->sendResponse();
        }
        return $resultRedirect;
    }
}
