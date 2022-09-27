<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Infosys\Vehicle\Model\Import\Vehicle\ProcessingErrorAggregator;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Infosys\Vehicle\Api\Data\VehicleProductMappingInterface;
use Infosys\Vehicle\Api\Data\VehicleInterface;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory;
use Infosys\Vehicle\Model\Import\Vehicle\VehicleProcessor;
use Infosys\Vehicle\Model\Import\Vehicle\RowValidatorInterface as ValidatorInterface;

class VehicleProductImport extends AbstractEntity
{
    const ENTITY_CODE = 'product_vehicle_mapping';

    const TABLE = VehicleProductMappingInterface::VEHICLE_PRODUCT_MAPPING_TABLE;

    const ENTITY_ID_COLUMN = VehicleProductMappingInterface::ID;

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = false;

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * Permanent entity columns.
     *
     * @var array
     */
    protected $_permanentAttributes = [
        'sku',
        'model_year_code'
    ];

    /**
     * Valid column names
     *
     * @var array
     */
    protected $validColumnNames = [
        'product_id',
        'vehicle_id'
    ];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var ResourceModelFactory
     */
    private $resourceFactory;
    /**
     * @var VehicleProcessor
     */
    private $vehicleProcessor;
    /**
     * @var array
     */
    private $_vehicleData = [];
    /**
     * Constructor function
     *
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregator $errorAggregator
     * @param ResourceModelFactory $resourceFactory
     * @param VehicleProcessor $vehicleProcessor
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregator $errorAggregator,
        ResourceModelFactory $resourceFactory,
        VehicleProcessor $vehicleProcessor
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->resourceFactory = $resourceFactory;
        $this->vehicleProcessor = $vehicleProcessor;
        $this->_initVehicles();
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }
    /**
     * Initialize existent vehicles.
     *
     * @return $this
     */
    protected function _initVehicles()
    {
        $this->_vehicleData = $this->vehicleProcessor->getVehicles();
        return $this;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->deleteEntity();
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->saveAndReplaceEntity();
                break;
            case Import::BEHAVIOR_APPEND:
                $this->saveAndReplaceEntity();
                break;
        }

        return true;
    }

    /**
     * Delete entities
     *
     * @return bool
     */
    private function deleteEntity(): bool
    {
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);

                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowId = $rowData[static::ENTITY_ID_COLUMN];
                    $rows[] = $rowId;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }

        if ($rows) {
            return $this->deleteEntityFinish(array_unique($rows));
        }

        return false;
    }

    /**
     * Save and replace entities
     *
     * @return void
     */
    private function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            $productIdLists = [];
            foreach ($bunch as $rowNum => $row) {
                if (isset($row['model_year_code'])) {
                    $rowId = $row['sku'];
                    $rows[] = $rowId;
                    $columnValues = [];
                    if ($this->getProductId($row['sku'])) {
                        $productId = $this->getProductId($row['sku']);
                        $productIdList['product_id'] = $productId;
                        $productIdLists[] = $productIdList;
                        $vehicleIds = $this->getVehicleIds($row['model_year_code'], $rowNum);
                        foreach ($vehicleIds as $vehicleId) {
                            $columnValues['product_id'] = $productId;
                            $columnValues['vehicle_id'] = $vehicleId;
                            $entityList[$productId][] = $columnValues;
                        }
                    }
                }
            }
            $this->saveEntityFinish($entityList);
            if ($productIdLists) {
                $this->connection->insertOnDuplicate('vehicle_fits_queue', $productIdLists);
            }
        }
    }

    /**
     * Save entities
     *
     * @param array $entityData
     *
     * @return bool
     */
    private function saveEntityFinish(array $entityData): bool
    {
        if ($entityData) {
            $tableName = $this->connection->getTableName(static::TABLE);
            $rows = [];

            foreach ($entityData as $entityRows) {
                foreach ($entityRows as $row) {
                    $rows[] = $row;
                }
            }
            if ($rows) {
                $newData = $this->connection->insertOnDuplicate($tableName, $rows, $this->getAvailableColumns());
                $this->countItemsCreated = $newData;
                $this->countItemsUpdated = count($rows) - $newData;

                return true;
            }
        }
        return false;
    }
    /**
     * Get product Id
     *
     * @param string $sku
     * @return int
     */
    private function getProductId($sku)
    {
        $entityTable = $this->resourceFactory->create()->getEntityTable();
        $select = $this->connection->select()->from(
            $entityTable
        )->where(
            $this->connection->quoteInto('sku IN (?)', $sku)
        );
        $roduct = $this->connection->fetchOne($select);
        return $roduct;
    }
    /**
     * Get All Vehicle Ids
     *
     * @param string $modeYearCodes
     * @param int $rowNum
     * @return array
     */
    private function getVehicleIds($modeYearCodes, $rowNum)
    {
        $allModelValues = [];
        $modeYearCodes =  explode(",", $modeYearCodes);
        foreach ($modeYearCodes as $code) {
            $modelValues = explode(":", $code);
            $allModelValues[$modelValues[0]] = explode("|", $modelValues[1]);
        }
        $modelYearCodeValues = [];
        foreach ($allModelValues as $modelCode => $modelYears) {
            foreach ($modelYears as $year) {
                $this->validateVehicle($year . '-' . $modelCode, $rowNum);
                $modelYearCode = '(' . $modelCode . ',' . $year . ')';
                $modelYearCodeValues[] = $modelYearCode;
            }
        }
        $modelYearCodeValues = implode(',', $modelYearCodeValues);
        $modelYearCodeValues = "(" . $modelYearCodeValues . ")";

        $tableName = $this->connection->getTableName(VehicleInterface::VEHICLE_TABLE);
        $select = $this->connection->select()->from(
            $tableName
        )->where("(model_code, model_year) IN " . $modelYearCodeValues);
        $vehicleIds = $this->connection->fetchCol($select);
        return $vehicleIds;
    }
    /**
     * Validate Vehicle
     *
     * @param string $modelYearCode
     * @param int $rowNum
     * @return void
     */
    public function validateVehicle($modelYearCode, $rowNum)
    {
        if (!in_array($modelYearCode, $this->_vehicleData)) {
            $this->addRowError(ValidatorInterface::ERROR_MODEL_YEAR_CODE_NOT_FOUND, $rowNum, 'model_year_code', $modelYearCode);
        }
    }
    /**
     * Delete entities
     *
     * @param array $entityIds
     *
     * @return bool
     */
    private function deleteEntityFinish(array $entityIds): bool
    {
        if ($entityIds) {
            try {
                $this->countItemsDeleted += $this->connection->delete(
                    $this->connection->getTableName(static::TABLE),
                    $this->connection->quoteInto(static::ENTITY_ID_COLUMN . ' IN (?)', $entityIds)
                );

                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return $this->validColumnNames;
    }
}
