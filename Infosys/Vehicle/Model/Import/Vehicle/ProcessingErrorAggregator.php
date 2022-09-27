<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\Import\Vehicle;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

/**
 * Import/Export Error Aggregator class
 */
class ProcessingErrorAggregator extends \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator
{
    /**
     * Add Error to csv file
     *
     * @param string $errorCode
     * @param string $errorLevel
     * @param int $rowNumber
     * @param string $columnName
     * @param string $errorMessage
     * @param string $errorDescription
     * @return void
     */
    public function addError(
        $errorCode,
        $errorLevel = ProcessingError::ERROR_LEVEL_CRITICAL,
        $rowNumber = null,
        $columnName = null,
        $errorMessage = null,
        $errorDescription = null
    ) {
        $this->processErrorStatistics($errorLevel);
        if ($errorLevel == ProcessingError::ERROR_LEVEL_CRITICAL) {
            $this->processInvalidRow($rowNumber);
        }
        $errorMessage = $this->getErrorMessage($errorCode, $errorMessage, $columnName);

        /** @var ProcessingError $newError */
        $newError = $this->errorFactory->create();
        $newError->init($errorCode, $errorLevel, $rowNumber, $columnName, $errorMessage, $errorDescription);
        $this->items['rows'][$rowNumber][] = $newError;
        $this->items['codes'][$errorCode][] = $newError;
        $this->items['messages'][$errorMessage][] = $newError;
        return $this;
    }
}
