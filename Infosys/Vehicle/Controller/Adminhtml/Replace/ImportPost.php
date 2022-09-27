<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Controller\Adminhtml\Replace;

use Magento\Framework\Controller\ResultFactory;
use Infosys\Vehicle\Model\CSVImport;
use Magento\Backend\App\Action\Context;
use Infosys\Vehicle\Logger\VehicleLogger;

/**
 * Import vehicle replace data
 */
class ImportPost extends \Magento\Backend\App\Action
{

    protected CSVImport $importHandler;
    protected VehicleLogger $logger;

    /**
     * @param Context $context
     * @param CSVImport $importHandler
     * @param VehicleLogger $logger
     */
    public function __construct(
        Context $context,
        CSVImport $importHandler,
        VehicleLogger $logger
    ) {
        $this->importHandler = $importHandler;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Import vehicle replace data
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $importVehicleReplaceDataFile = $this->getRequest()->getFiles('import_vehicle_replace_data_file');

        if ($this->getRequest()->isPost() && isset($importVehicleReplaceDataFile['tmp_name'])) {
            try {            
                $this->importHandler->importFromCsvFile($importVehicleReplaceDataFile);
                $this->messageManager->addSuccess(__('The vehicle attribute replace data was imported.'));
                $this->logger->info("Vehicle Replace Data Import : The vehicle attribute replace data was imported.");
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invalid file upload attempt'));
            }
        } else {
            $this->messageManager->addError(__('Invalid file upload attempt'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }

    /**
     * Checking the authentication for access
     */
    protected function _isAllowed() : bool
    {
        return $this->_authorization->isAllowed(
            'Infosys_Vehicle::data_replace'
        );
    }
}
