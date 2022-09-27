<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\History as ModelHistory;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\Report\ReportProcessorInterface;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const XML_LOG_ENABLED = 'epc_config/logging_errors/active';

    const XML_MAX_PRODUCT_COUNT = 'epc_config/product_vehicle_fitment/maximum_product_count';

    const XML_VEHICLE_DATA_FIND_REPLACE = 'epc_config/vehicle_data_replace/vehicle_data_find_replace';

    /**
     * Constructor function
     *
     * @param Context $context
     * @param ModelHistory $historyModel
     * @param Report $reportHelper
     * @param ReportProcessorInterface $reportProcessor
     */
    public function __construct(
        Context $context,
        ModelHistory $historyModel,
        Report $reportHelper,
        ReportProcessorInterface $reportProcessor
    ) {
        parent::__construct($context);
        $this->historyModel = $historyModel;
        $this->reportHelper = $reportHelper;
        $this->reportProcessor = $reportProcessor;
    }

    /**
     * @inheritdoc
     */
    public function isLogEnabled()
    {
        $isEnabled = $this->scopeConfig->getValue(
            self::XML_LOG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        return $isEnabled;
    }

    /**
     * @inheritdoc
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Create Error csv file for schedule import
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return void
     */
    public function createErrorReport(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $this->historyModel->loadLastInsertItem();
        $sourceFile = $this->reportHelper->getReportAbsolutePath($this->historyModel->getImportedFile());
        $writeOnlyErrorItems = true;
        if ($this->historyModel->getData('execution_time') == ModelHistory::IMPORT_VALIDATION) {
            $writeOnlyErrorItems = false;
        }
        $fileName = $this->reportProcessor->createReport($sourceFile, $errorAggregator, $writeOnlyErrorItems);
        $this->historyModel->addErrorReportFile($fileName);
    }

    /**
     * Function to get Maximum Products count for updating vehicle fitment
     */
    public function getMaxFitmentProductsCount(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_MAX_PRODUCT_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Method to check the vehicle data replace logic enabled/disabled
     */
    public function isReplaceLogicEnabled() : bool
    {
        $isLogicEnabled = $this->scopeConfig->getValue(
            self::XML_VEHICLE_DATA_FIND_REPLACE,
            ScopeInterface::SCOPE_STORE
        );
        return $isLogicEnabled;
    }

}
