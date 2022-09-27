<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare (strict_types = 1);

namespace Infosys\PriceAdjustment\Controller\Adminhtml\Percentage;
 
use Infosys\PriceAdjustment\Cron\UpdateMediaSetSelector;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class to run media set sync cron manually
 */
class RunMediaSetSyncCron extends \Magento\Backend\App\Action
{
    protected PageFactory $resultPageFactory;
    
    protected UpdateMediaSetSelector $updateMediaSetSelector;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param UpdateMediaSetSelector $updateMediaSetSelector
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        UpdateMediaSetSelector $updateMediaSetSelector
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->UpdateMediaSetSelector = $updateMediaSetSelector;
    }

    /**
     * Function to run  media set cron manually
     */
    public function execute(): object
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->UpdateMediaSetSelector->execute();
        $this->messageManager->addSuccess(__("Media Set Sync Cron Ran Successfully"));
        return $resultRedirect->setPath('*/*/', ['_current' => true, '_use_rewrite' => true]);
    }
    
    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Infosys_PriceAdjustment::price_ad');
    }
}
