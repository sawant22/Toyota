<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Controller\Adminhtml\Replace;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class ImportExport extends \Infosys\Vehicle\Controller\Adminhtml\Replace implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Infosys_Vehicle::data_replace';

    /**
     * Import and export Page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Magento_TaxImportExport::system_convert_tax');
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(\Infosys\Vehicle\Block\Adminhtml\Replace\ImportExport::class)
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Import and Export Vehicle Replace Data'));
        return $resultPage;
    }
}
