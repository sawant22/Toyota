<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\Import;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\File\Csv;
use Magento\ScheduledImportExport\Model\Scheduled\OperationFactory as ImportModel;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\DataObject;
use Magento\ScheduledImportExport\Model\Scheduled\Operation;
use Magento\ScheduledImportExport\Model\ResourceModel\Scheduled\Operation\CollectionFactory;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data;
use Magento\ScheduledImportExport\Model\ObserverFactory as ObserverModel;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class ScheduledVehicleProductImport
{

    const VEHICLE_PRODUCT_IMPORT_PATH = 'import/vehicle_product_mapping';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var WriteInterface
     */
    protected $newDirectory;
    /**
     * @var FileFactory
     */
    protected $fileFactory;
    /**
     * @var Csv
     */
    protected $csvProcessor;
    /**
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * @var ImportModel
     */
    protected $importModel;
    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var CollectionFactory
     */
    protected $scheduleImportFactory;
    /**
     * @var ObserverModel
     */
    protected $observerModel;
    /**
     * @var File
     */
    protected $file;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * Constructor function
     *
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param Csv $csvProcessor
     * @param DirectoryList $directoryList
     * @param ImportModel $importModel
     * @param DateTime $dateTime
     * @param CollectionFactory $scheduleImportFactory
     * @param ObserverModel $observerModel
     * @param File $file
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        Filesystem $filesystem,
        FileFactory $fileFactory,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        ImportModel $importModel,
        DateTime $dateTime,
        CollectionFactory $scheduleImportFactory,
        ObserverModel $observerModel,
        File $file,
        ScopeConfigInterface $scopeConfigInterface,
        LoggerInterface $logger
    ) {
        $this->newDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->importModel = $importModel;
        $this->dateTime = $dateTime;
        $this->scheduleImportFactory = $scheduleImportFactory;
        $this->observerModel = $observerModel;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->scopeConfig = $scopeConfigInterface;
        $this->logger = $logger;
    }

    /**
     * Create new folder
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function createDirectory()
    {
        $newDirectory = false;
        try {
            $newDirectory = $this->newDirectory->create(self::VEHICLE_PRODUCT_IMPORT_PATH);
        } catch (FileSystemException $e) {
            throw new LocalizedException(
                __('We can\'t create directory "%1"', self::VEHICLE_PRODUCT_IMPORT_PATH)
            );
        }
        return $newDirectory;
    }

    /**
     * Create new csv file
     *
     * @param array $Mappingdata
     * @param string $mappingFileName
     * @return void
     */
    public function createImportCSVFile($Mappingdata, $mappingFileName)
    {
        $this->createDirectory();
        $content[] = [
            'sku' => __('sku'),
            'model_year_code' => __('model_year_code')
        ];
        $filePath =  $this->directoryList->getPath(DirectoryList::VAR_DIR) . "/" .
            self::VEHICLE_PRODUCT_IMPORT_PATH . "/" .
            'vehicle_product_mapping_' . $mappingFileName . '.csv';

        foreach ($Mappingdata as $data) {
            if (isset($data['model_year_code'])) {
                $content[] = [$data['sku'], $data['model_year_code']];
            }
        }
        $this->csvProcessor->setEnclosure('"')->setDelimiter(',')->saveData($filePath, $content);
    }
    /**
     * Create Scheduled Import
     *
     * @param string $behavior
     * @param string $mappingFileName
     * @return void
     */
    public function createScheduledImport($behavior, $mappingFileName)
    {
        $hours = $this->dateTime->gmtDate("H:i:s");
        $operation = $this->importModel->create();
        $email = $this->scopeConfig->getValue(
            'epc_config/epc_config_group/scheduled_import_email'
        );
        $fileinfo = [
            "_import_field_separator" => ",",
            "_import_multiple_value_separator" => ",",
            "server_type" => "file",
            "file_path" => DirectoryList::VAR_DIR . '/' . self::VEHICLE_PRODUCT_IMPORT_PATH,
            "file_name" =>  'vehicle_product_mapping_' . $mappingFileName . '.csv',
            "import_images_file_dir" => "",
        ];
        $operation->setData('name', 'Vehicle Product Mapping')
            ->setData('operation_type', "import")
            ->setData('entity_type', 'product_vehicle_mapping')
            ->setData('behavior', $behavior)
            ->setData('start_time', $hours)
            ->setData('freq', "D")
            ->setData('force_import', 1)
            ->setData('file_info', $fileinfo)
            ->setData('status', 1)
            ->setData('email_receiver', "general")
            ->setData('email_sender', "general")
            ->setData('email_template', "magento_scheduledimportexport_import_failed")
            ->setData('email_copy_method', "bcc")
            ->setEmailCopy($email)
            ->save();
        $schedule = new DataObject();
        $schedule->setJobCode(
            Operation::CRON_JOB_NAME_PREFIX . $operation->getId()
        );
    }
    /**
     * Run Scheduled Import
     *
     * @return void
     */
    public function runScheduledImport()
    {
        $collection = $this->scheduleImportFactory->create()
            ->addFieldToFilter('entity_type', ['eq' => 'product_vehicle_mapping'])
            ->addFieldToFilter('is_success', ['eq' => Data::STATUS_PENDING]);
        $importMappingFiles = [];
        if ($collection->getSize()) {
            foreach ($collection as $scheduleData) {
                try {
                    $schedule = new DataObject();
                    $schedule->setJobCode(
                        Operation::CRON_JOB_NAME_PREFIX . $scheduleData->getId()
                    );
                    $jobresult = $this->observerModel->create()->processScheduledOperation($schedule, true);
                    if ($jobresult) {
                        $fileInfo = $scheduleData->getFileInfo();
                        $importMappingFiles[] = $fileInfo['file_name'];
                        $scheduleData->delete();
                    }
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                }
            }
            foreach ($importMappingFiles as $importedFiles) {
                $fileDirectory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
                $mediaRootDir = $fileDirectory->getAbsolutePath() . self::VEHICLE_PRODUCT_IMPORT_PATH;
                if ($this->file->isExists($mediaRootDir . '/' . $importedFiles)) {
                    $this->file->deleteFile($mediaRootDir . '/' . $importedFiles);
                }
            }
        }
    }
}
