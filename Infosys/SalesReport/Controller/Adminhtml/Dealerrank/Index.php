<?php
/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\SalesReport\Controller\Adminhtml\Dealerrank;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class to initiate the dealer rank report grid
 */
class Index extends Action
{
    protected PageFactory $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Dealerrank report action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Dealer Ranking Report'));
        $this->_view->renderLayout();
    }
}