<?php

/**
 * @package   Infosys/Vehicle
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ScheduledImportExport\Model\ResourceModel\Scheduled\Operation\CollectionFactory;
use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data;
use Magento\ScheduledImportExport\Model\Scheduled\OperationFactory as ImportModel;
use Magento\ScheduledImportExport\Model\Scheduled\Operation;
use Magento\Framework\DataObject;
use Magento\ScheduledImportExport\Model\ObserverFactory as ObserverModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Infosys\Vehicle\Logger\VehicleLogger;
use Infosys\Vehicle\Model\SftpConnection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\Glob;

class Scheduler
{
    const VEHICLE_FILES_PATH = 'import/epc_schedule_import';
    const SCHEDULE_TASKS_TABLE = 'vehicle_schedule_tasks';
    const FILE_EXPIRY_LIMIT = 10;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var VehicleLogger
     */
    protected $logger;

    /**
     * @var WriteInterface
     */
    protected $newDirectory;

    /**
     * @var CollectionFactory
     */
    protected $scheduleFactory;

    /**
     * @var ObserverModel
     */
    protected $observerModel;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var ImportModel
     */
    protected $importModel;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * @var SftpConnection
     */
    protected $SftpConnection;

    /**
     * @var \Infosys\Vehicle\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Filesystem\Glob
     */
    protected $glob;

    /**
     * Constructor function
     *
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param CollectionFactory $scheduleFactory
     * @param ObserverModel $observerModel
     * @param ImportModel $importModel
     * @param DateTime $dateTime
     * @param File $file
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param VehicleLogger $logger
     * @param SftpConnection $SftpConnection
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Infosys\Vehicle\Helper\Data $helperData
     * @param \Magento\Framework\Filesystem\Glob $glob
     */
    public function __construct(
        Filesystem $filesystem,
        DirectoryList $directoryList,
        CollectionFactory $scheduleFactory,
        ObserverModel $observerModel,
        ImportModel $importModel,
        DateTime $dateTime,
        File $file,
        ScopeConfigInterface $scopeConfigInterface,
        VehicleLogger $logger,
        SftpConnection $SftpConnection,
        \Magento\Framework\App\ResourceConnection $resource,
        \Infosys\Vehicle\Helper\Data $helperData,
        \Magento\Framework\Filesystem\Glob $glob
    ) {
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->scheduleFactory = $scheduleFactory;
        $this->observerModel = $observerModel;
        $this->importModel = $importModel;
        $this->dateTime = $dateTime;
        $this->file = $file;
        $this->scopeConfig = $scopeConfigInterface;
        $this->logger = $logger;
        $this->SftpConnection = $SftpConnection;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->helperData = $helperData;
        $this->glob = $glob;
        $this->newDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Create schedule task
     *
     * @param string $file
     * @return string
     */
    public function createScheduleTask($file)
    {
        $hours = $this->dateTime->gmtDate("H:i:s");
        $operation = $this->importModel->create();
        $file_path = DirectoryList::VAR_DIR.'/'.self::VEHICLE_FILES_PATH;
        $name = $this->scheduleTaskName($file);
        $behavior = $this->importBehaviour($file);
        $entity = $this->importEntity($file);
        $email = $this->scopeConfig->getValue(
            'epc_config/epc_config_group/scheduled_import_email'
        );
        $force_import = $this->helperData->getConfig('epc_config/import_settings/validation_type');
        
        $fileinfo = [
            "_import_field_separator" => ",",
            "_import_multiple_value_separator" => "|",
            "server_type" => "file",
            "file_path" => $file_path,
            "file_name" => $file,
            "import_images_file_dir" => "",
        ];
        $operation->setData('name', $name)
            ->setData('operation_type', "import")
            ->setData('entity_type', $entity)
            ->setData('behavior', $behavior)
            ->setData('start_time', $hours)
            ->setData('freq', "D")
            ->setData('force_import', $force_import)
            ->setData('file_info', $fileinfo)
            ->setData('status', 0)
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
        return $operation->getId();
    }

    /**
     * Check Import Behaviour
     *
     * @param string $file
     * @return string
     */
    public function importBehaviour($file)
    {
        switch ($file) {
            case (preg_match("/add/i", $file) ? true : false):
                $behavior = 'append';
                break;
            case (preg_match("/delete/i", $file) ? true : false):
                $behavior = 'delete';
                break;
            case (preg_match("/replace/i", $file) ? true : false):
                $behavior = 'replace';
                break;
            default:
                $behavior = 'append';
        }

        return $behavior;
    }

    /**
     * Check Entity Type
     *
     * @param string $file
     * @return string
     */
    public function importEntity($file)
    {
        switch ($file) {
            case (preg_match("/products/i", $file) ? true : false):
                $entity_type = 'catalog_product';
                break;
            case (preg_match("/vehicles/i", $file) ? true : false):
                $entity_type = 'vehicle';
                break;
            default:
                $entity_type = '';
        }

        return $entity_type;
    }

    /**
     * Schedule Task Name
     *
     * @param string $file
     * @return string
     */
    public function scheduleTaskName($file)
    {
        switch ($file) {
            case (preg_match("/products/i", $file) ? true : false):
                $name = 'Products Import';
                break;
            case (preg_match("/vehicles/i", $file) ? true : false):
                $name = 'Vehicle Import';
                break;
            default:
                $name = 'Import';
        }

        return $name;
    }

    /**
     * Sync files
     *
     * @return mixed
     */
    public function syncScheduleFiles()
    {
        //Auto remove scheduled files older than x days
        $importRootDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . "/" .self::VEHICLE_FILES_PATH . "/";
        $file_expiry_limit = $this->helperData->getConfig('epc_config/cron_settings/file_expiry_limit');

        if (!$file_expiry_limit) {
            $file_expiry_limit = self::FILE_EXPIRY_LIMIT;
        }

        //loop through all files in the directory
        foreach ($this->glob->glob($importRootDir."*") as $file) {
            $expiry_time = $file_expiry_limit*24*60*60;
            $created_time = filemtime($file);
            $current_time = strtotime($this->dateTime->gmtDate());
            
            //check if files are x days older then delete it
            if (($current_time - $created_time) > $expiry_time &&
                $this->file->isExists($file)
            ) {
                $this->file->deleteFile($file);
            }
        }

        //set connection
        $sftp = $this->SftpConnection->connection();
        $files = $this->SftpConnection->getFile($sftp);

        //create directory
        $this->createDirectory();

        //check if array not empty
        if (!empty($files)) {

            //sort files by modified date
            usort($files, function ($a, $b) {
                return $a['mtime'] > $b['mtime'];
            });

            $data = [];

            //copy files in local directory
            foreach ($files as $file_arr) {
                $file = $file_arr['filename'];
                $file_type = $this->importEntity($file);

                $filesPath =  $this->directoryList->getPath(DirectoryList::VAR_DIR) . "/" .
                    self::VEHICLE_FILES_PATH . "/" .$file;
                
                //check if file not copied already
                if (!$this->fileExist($file)) {
                    $created_at_sftp = $this->dateTime->gmtDate('Y-m-d h:i:s', $file_arr['mtime']);
                    
                    if ($sftp->get($file, $filesPath)) {
                        $data[] = ['file_name' => $file,
                            'file_type' => $file_type,
                            'status' => 0,
                            'created_at_sftp' => $created_at_sftp,
                        ];
                        $this->logger->info('Copied file '.$file);
                    }
                }
            }
            
            //check data exist
            if ($data) {
                //store files data
                $this->storeScheduledFile($data);
                $this->logger->info('Files copied and stored successfully.');
            }
        } else {
            $this->logger->warning('No files available at s3 bucket.');
        }
    }

    /**
     * Create schedule and run scheduled files
     *
     * @return void
     */
    public function scheduledTasks()
    {
        //check if any other file already running
        if ($this->checkRunningFiles()) {
            $this->logger->info('Files still in progress.');
            return true;
        }

        $file_data = $this->getVehicleFile();
        //select product file if no vehicle file in queue
        if (empty($file_data)) {
            $file_data = $this->getProductFile();
        }

        if ($file_data) {
            $file = $file_data['file_name'];

            //create schedule task and update status as running
            $operation_id = $this->createScheduleTask($file);
            $this->logger->info('Created scheduled task for '.$file);
            $this->updateScheduleStatus(
                $file,
                [
                    'status' => 1,
                    'scheduled_at' => (new \DateTime())->format('Y-m-d h:i:s')
                ]
            );

            try {
                $schedule = new DataObject();
                $schedule->setJobCode(
                    Operation::CRON_JOB_NAME_PREFIX . $operation_id
                );

                //Run scheduled task
                $jobresult = $this->observerModel->create()->processScheduledOperation($schedule, true);
                if ($jobresult) {
                    //update status if task completed
                    $this->updateScheduleStatus($file, ['status' => 2]);
                    $this->logger->info('Run scheduled task for '.$file);

                    $task_collection = $this->scheduleFactory->create()
                        ->addFieldToFilter('id', ['eq' => $operation_id]);

                    $task_collection->walk('delete');
                } else {
                    $this->updateScheduleStatus($file, ['status' => 3]);
                    $this->logger->info('Failed scheduled task for '.$file);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * Check if any file running for import
     *
     * @return array
     */
    protected function checkRunningFiles()
    {
        $task_time = $this->dateTime->gmtDate('Y-m-d h:i:s', strtotime('-1 hour'));
        $select = $this->_connection->select()->from(
            self::SCHEDULE_TASKS_TABLE
        )->where('status= (?)', 1)
         ->where('scheduled_at >= (?)', $task_time); // skip file if running for more than 1 hour
        $data = $this->_connection->fetchRow($select);
        return $data;
    }

    /**
     * Check if file already exist
     *
     * @param string $file
     * @return array
     */
    protected function fileExist($file)
    {
        $select = $this->_connection->select()->from(
            self::SCHEDULE_TASKS_TABLE
        )->where('file_name= (?)', $file);
        $data = $this->_connection->fetchRow($select);
        return $data;
    }

    /**
     * Update file schedule status
     *
     * @param string $file
     * @param array $update_data
     * @return void
     */
    protected function updateScheduleStatus($file, $update_data)
    {
        $this->_connection->update(
            self::SCHEDULE_TASKS_TABLE,
            $update_data,
            ['file_name = ?' => $file]
        );
    }

    /**
     * Get vehicle file
     *
     * @return array
     */
    protected function getVehicleFile()
    {
        $select = $this->_connection->select()->from(
            self::SCHEDULE_TASKS_TABLE
        )->where('file_type= (?)', 'vehicle')
        ->where('status= (?)', 0);
        $data = $this->_connection->fetchRow($select);
        return $data;
    }

    /**
     * Get product file
     *
     * @return array
     */
    protected function getProductFile()
    {
        $select = $this->_connection->select()->from(
            self::SCHEDULE_TASKS_TABLE
        )->where('file_type= (?)', 'catalog_product')
        ->where('status= (?)', 0);
        $data = $this->_connection->fetchRow($select);
        return $data;
    }

    /**
     * Store Scheduled files
     *
     * @param array $data
     * @return mixed
     */
    protected function storeScheduledFile($data)
    {
        try {
            $this->_connection->insertOnDuplicate(self::SCHEDULE_TASKS_TABLE, $data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Create new directory
     *
     * @return bool
     * @throws LocalizedException
     */
    public function createDirectory()
    {
        $newDirectory = false;
        try {
            $newDirectory = $this->newDirectory->create(self::VEHICLE_FILES_PATH);
        } catch (FileSystemException $e) {
            throw new LocalizedException(
                __('We can\'t create directory "%1"', self::VEHICLE_FILES_PATH)
            );
        }
        return $newDirectory;
    }
}
