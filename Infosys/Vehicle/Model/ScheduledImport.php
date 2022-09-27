<?php

/**
 * @package   Infosys/Vehicle
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\History;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Helper\Data as DataHelper;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\Import\ConfigInterface;
use Magento\ImportExport\Model\Import\Entity\Factory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\ImportExport\Model\Source\Import\Behavior\Factory as BehaviorFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;
use Infosys\Vehicle\Helper\Data as Helper;
use Magento\ImportExport\Model\Import\AbstractSource;

/**
 * Scheduled import model
 */
class ScheduledImport extends \Magento\ScheduledImportExport\Model\Import
{
    /**
     * @varHelper
     */
    protected $helper;
    /**
     * Constructor function
     *
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param DataHelper $importExportData
     * @param ScopeConfigInterface $coreConfig
     * @param ConfigInterface $importConfig
     * @param Factory $entityFactory
     * @param Data $importData
     * @param CsvFactory $csvFactory
     * @param FileTransferFactory $httpFactory
     * @param UploaderFactory $uploaderFactory
     * @param BehaviorFactory $behaviorFactory
     * @param IndexerRegistry $indexerRegistry
     * @param History $importHistoryModel
     * @param DateTime $localeDate
     * @param Helper $helper
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        DataHelper $importExportData,
        ScopeConfigInterface $coreConfig,
        ConfigInterface $importConfig,
        Factory $entityFactory,
        Data $importData,
        CsvFactory $csvFactory,
        FileTransferFactory $httpFactory,
        UploaderFactory $uploaderFactory,
        BehaviorFactory $behaviorFactory,
        IndexerRegistry $indexerRegistry,
        History $importHistoryModel,
        DateTime $localeDate,
        Helper $helper
    ) {
        parent::__construct(
            $logger,
            $filesystem,
            $importExportData,
            $coreConfig,
            $importConfig,
            $entityFactory,
            $importData,
            $csvFactory,
            $httpFactory,
            $uploaderFactory,
            $behaviorFactory,
            $indexerRegistry,
            $importHistoryModel,
            $localeDate
        );

        $this->helper = $helper;
    }
    /**
     * Run import through cron
     *
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation $operation
     * @return bool
     */
    public function runSchedule(\Magento\ScheduledImportExport\Model\Scheduled\Operation $operation)
    {
        $sourceFile = $operation->getFileSource($this);
        $errorAggregator = $this->getErrorAggregator();

        if ($sourceFile) {
            $validationStrategy = $operation->getForceImport()
                ? ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS
                : ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR;

            $this->setData(self::FIELD_NAME_VALIDATION_STRATEGY, $validationStrategy);
            $this->_removeBom($sourceFile);
            $this->createHistoryReport($sourceFile, $operation->getEntityType());

            $result = $this->validateFileContent(
                \Magento\ImportExport\Model\Import\Adapter::findAdapterFor(
                    $sourceFile,
                    $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT),
                    $this->getData(\Magento\ImportExport\Model\Import::FIELD_FIELD_SEPARATOR)
                )
            );

            if ($result
                || $operation->getForceImport()
                && !$errorAggregator->hasFatalExceptions()
            ) {
                $errorAggregator->clear();
                $result = $this->importSource();
            }

            if ($result) {
                $this->invalidateIndex();
            }

            if ($errorAggregator->getErrorsCount()) {
                $result = false;
                $this->helper->createErrorReport($errorAggregator);
            }
           
            return (bool)$result;
        }

        return false;
    }

    /**
     * Validates source file and returns validation result
     *
     * 'validation strategy' and 'allowed error count' values to allow using this parameters in validation process.
     *
     * @param AbstractSource $source
     * @return bool
     * @throws LocalizedException
     */
    public function validateFileContent(AbstractSource $source) : bool
    {
        $errors_count_limit = $this->helper->getConfig('epc_config/import_settings/errors_count');

        $errorAggregator = $this->getErrorAggregator();
        $errorAggregator->initValidationStrategy(
            $this->getData(self::FIELD_NAME_VALIDATION_STRATEGY),
            $errors_count_limit
        );

        try {
            $adapter = $this->_getEntityAdapter()->setSource($source);
            $adapter->validateData();
        } catch (\Exception $e) {
            $errorAggregator->addError(
                AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                $e->getMessage()
            );
        }

        $messages = $this->getOperationResultMessages($errorAggregator);
        $this->addLogComment($messages);

        $result = !$errorAggregator->isErrorLimitExceeded();

        return $result;
    }
}
