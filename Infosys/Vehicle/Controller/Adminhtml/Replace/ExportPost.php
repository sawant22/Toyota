<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Controller\Adminhtml\Replace;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Infosys\Vehicle\Model\ResourceModel\VehicleReplaceData\Collection;
use Infosys\Vehicle\Logger\VehicleLogger;

/**
 * Export vehicle replace data
 */
class ExportPost extends \Magento\Backend\App\Action
{

    protected Context $context;
    protected FileFactory $fileFactory;
    protected Collection $collection;
    protected VehicleLogger $logger;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Collection $collection
     * @param VehicleLogger $logger
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Collection $collection,
        VehicleLogger $logger
    ) {
        $this->fileFactory = $fileFactory;
        $this->collection = $collection;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Export action from import/export vehicle replace data
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        try {
            /** start csv content and set template */
            $headers = new \Magento\Framework\DataObject(
                [
                    'attribute' => __('Attribute'),
                    'find' => __('Find'),
                    'replace' => __('Replace')
                ]
            );
            $template = '"{{attribute}}","{{find}}","{{replace}}"';
            $content = $headers->toString($template);
            $content .= "\n";

            while ($replaceData = $this->collection->fetchItem()) {
                $content .= $replaceData->toString($template) . "\n";
            }
            // pass 'rm' parameter to delete a file after download
            $fileContent = ['type' => 'string', 'value' => $content, 'rm' => true];

            $export = $this->fileFactory->create('vehicle_attribute_replace_data.csv', $fileContent, DirectoryList::VAR_DIR);
            $this->logger->info("Vehicle Replace Data Export : The vehicle attribute replace data was exported.");
            return $export;


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Invalid file upload attempt'));
        }
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
