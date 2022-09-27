<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\Import;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\ResourceConnection;
use Infosys\Vehicle\Model\Import\Vehicle\RowValidatorInterface as ValidatorInterface;
use Infosys\Vehicle\Model\Import\Vehicle\VehicleProcessor;
use Magento\ImportExport\Model\Import;
use Infosys\Vehicle\Logger\VehicleLogger;
use Infosys\Vehicle\Helper\Data as VehicleHelper;
use Infosys\Vehicle\Api\Data\VehicleProductMappingInterface;
use Infosys\Vehicle\Model\CSVImport;

/**
 * Vehicle entity product model
 */
class Vehicle extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const ENTITY_ID_COLUMN = 'entity_id';

    const VEHICLE_TABLE = 'catalog_vehicle_entity';

    const VEHICLE_REPLACE_TABLE = 'vehicle_data_replace';

    const TITLE = 'title';

    const BRAND = 'brand';

    const MODEL_YEAR = 'model_year';

    const MODEL_CODE = 'model_code';

    const SERIES_NAME = 'series_name';

    const GRADE = 'grade';

    const DRIVELINE = 'driveline';

    const BODY_STYLE = 'body_style';

    const ENGINE_TYPE = 'engine_type';

    const MODEL_RANGE = 'model_range';

    const MODEL_DESCRIPTION = 'model_description';

    const TRANSMISSION = 'transmission';

    const DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT = 'null';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_MODEL_YEAR_IS_EMPTY => 'Model year is empty',
        ValidatorInterface::ERROR_MODEL_CODE_IS_EMPTY => 'Model code is empty',
    ];

    /**
     * Required attributes
     *
     * @var array
     */
    protected $_permanentAttributes = [self::MODEL_YEAR, self::MODEL_CODE];

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * Valid columns
     *
     * @var array
     */
    protected $validColumnNames = [
        self::TITLE,
        self::BRAND,
        self::MODEL_YEAR,
        self::MODEL_CODE,
        self::SERIES_NAME,
        self::GRADE,
        self::DRIVELINE,
        self::BODY_STYLE,
        self::ENGINE_TYPE,
        self::MODEL_RANGE,
        self::MODEL_DESCRIPTION,
        self::TRANSMISSION
    ];


    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    /**
     * Validators
     *
     * @var array
     */
    protected $_validators = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_connection;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     */
    protected $_resource;

    /**
     * @var VehicleProcessor
     */
    protected $vehicleProcessor;

    /**
     * Existing vehicles
     *
     * @var array
     */
    protected $_oldVehicles = [];

    /**
     * @var VehicleLogger
     */
    protected $logger;

    protected VehicleHelper $vehicleHelper;

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param VehicleProcessor $vehicleProcessor
     * @param VehicleLogger $logger
     * @param VehicleHelper $vehicleHelper
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        VehicleProcessor $vehicleProcessor,
        VehicleLogger $logger,
        VehicleHelper $vehicleHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->vehicleProcessor = $vehicleProcessor;
        $this->logger = $logger;
        $this->vehicleHelper = $vehicleHelper;
        $this->_initVehicles();
    }

    /**
     * Initialize existent vehicles.
     *
     * @return $this
     */
    protected function _initVehicles()
    {
        $this->_oldVehicles = $this->vehicleProcessor->getVehicles();
        return $this;
    }

    /**
     * Check if vehicle exists for specified model_year and model_code
     *
     * @param string $vehicle_year_code
     * @return bool
     */
    private function isVehicleExist($vehicle_year_code)
    {
        return in_array($vehicle_year_code, $this->_oldVehicles);
    }

    /**
     * Valid column names
     *
     * @return array
     */
    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'vehicle';
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        if (!isset($rowData[self::MODEL_YEAR]) || empty($rowData[self::MODEL_YEAR])) {
            $this->addRowError(ValidatorInterface::ERROR_MODEL_YEAR_IS_EMPTY, $rowNum);
            return false;
        }

        if (!isset($rowData[self::MODEL_CODE]) || empty($rowData[self::MODEL_CODE])) {
            $this->addRowError(ValidatorInterface::ERROR_MODEL_CODE_IS_EMPTY, $rowNum);
            return false;
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Create Advanced data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        $this->_validatedRows = null;
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteVehicle();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveVehicle();
        }

        return true;
    }

    /**
     * Save vehicle data
     *
     * @return $this
     */
    public function saveVehicle()
    {
        $this->saveAndReplaceVehicle();
        return $this;
    }

    /**
     * Deletes vehicle data from raw data.
     *
     * @return $this
     */
    public function deleteVehicle()
    {
        $listVehicle = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowVehicle = '(' . $rowData[self::MODEL_YEAR] . ',' . $rowData[self::MODEL_CODE] . ')';
                    $listVehicle[] = $rowVehicle;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($listVehicle) {
            $this->deleteVehicleFinish($listVehicle, self::VEHICLE_TABLE);
        }
        $this->logger->info("Vehicles Deleted : " . $this->countItemsDeleted);
        return $this;
    }

    /**
     * Save and replace
     *
     * @return $this
     */
    protected function saveAndReplaceVehicle()
    {
        $behavior = $this->getBehavior();
        $listVehicle = [];
        $duplicates = [];
        $duplicates_year_code = [];
        //Get the vehicle attribute replace data
        $vehicleAttributeReplaceData = $this->getVehicleReplaceData();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityCreateList = [];
            $entityUpdateList = [];

            foreach ($bunch as $rowNum => $rowData) {

                try {
                    $unique_vehicle = $rowData[self::MODEL_YEAR] . strtolower($rowData[self::SERIES_NAME]) .
                        strtolower($rowData[self::GRADE]) . strtolower($rowData[self::DRIVELINE]);
                    $unique_year_code = $rowData[self::MODEL_YEAR] . $rowData[self::MODEL_CODE];

                    if (!$this->validateRow($rowData, $rowNum)) {
                        $this->addRowError(ValidatorInterface::ERROR_MODEL_YEAR_IS_EMPTY, $rowNum);
                        continue;
                    }

                    /* if (in_array($unique_vehicle, $duplicates)) {
                    $this->addRowError(ValidatorInterface::ERROR_DUPLICATE_VEHICLE, $rowNum);
                    continue;
                }*/

                    if (in_array($unique_year_code, $duplicates_year_code)) {
                        $this->addRowError(ValidatorInterface::ERROR_DUPLICATE_YEAR_CODE, $rowNum);
                        continue;
                    }

                    $duplicates[] = $unique_vehicle;
                    $duplicates_year_code[] = $unique_year_code;

                    $rowData = $this->removeEmptyAttributeConstant($rowData);

                    $columns = [];
                    foreach ($this->getValidColumnNames() as $attributeKey) {
                        if (array_key_exists($attributeKey, $rowData)) {
                            $columns[$attributeKey] = trim($rowData[$attributeKey]);

                            //Replace the vehicle attribute value
                            if (
                                $this->vehicleHelper->isReplaceLogicEnabled()
                                && in_array($attributeKey, CSVImport::VALID_FIND_REPLACE_ATTRIBUTE_KEYS)
                            ) {
                                $find = trim($rowData[$attributeKey]);

                                //search replace data for match on attribute and find
                                $attributeReplaceData = array_filter($vehicleAttributeReplaceData, function ($replaceData)
                                use ($attributeKey, $find) {
                                    return ($replaceData[CSVImport::ATTRIBUTE] == $attributeKey and $replaceData[CSVImport::FIND] == $find);
                                });

                                //Update if we get a result
                                if ($attributeReplaceData && array_key_exists(CSVImport::REPLACE, array_values($attributeReplaceData)[0])) {
                                    $columns[$attributeKey] = trim(str_replace("  ", " ", array_values($attributeReplaceData)[0][CSVImport::REPLACE]));
                                }
                            }
                        }
                    }

                    $columns = $this->formatVehicleData($columns);


                    //If resulting Series is empty, report as error
                    if (empty($columns[self::SERIES_NAME])) {
                        $this->addRowError(ValidatorInterface::ERROR_SERIES_NAME_IS_EMPTY, $rowNum);
                    }

                    //If resulting Grade is empty, report as error and default to NONE
                    if (empty($columns[self::GRADE])) {
                        $this->addRowError(ValidatorInterface::WARNING_GRADE_EMPTY, $rowNum);
                        $columns[self::GRADE] = "NONE";
                    }

                    //If resulting Driveline is empty, report as error and default to NONE
                    if (empty($columns[self::DRIVELINE])) {
                        $this->addRowError(ValidatorInterface::WARNING_DRIVELINE_EMPTY, $rowNum);
                        $columns[self::DRIVELINE] = "NONE";
                    }

                    $vehicleData = $this->getVehicleDetails($rowData);

                    // check if vehicle already exist
                    if ($vehicleData) {
                        if (!$this->validateUpdateVehicle($rowData, $rowNum)) {
                            continue;
                        }
                        $columns = $this->updateVehicleRow($columns, $vehicleData);
                        $rowVehicle = $rowData[self::MODEL_YEAR];
                        $entityUpdateList[$rowVehicle][] = $columns;
                    } else {
                        if (!$this->validateNewVehicle($rowData, $rowNum)) {
                            continue;
                        }
                        $entityCreateList[] = $columns;
                    }
                } catch (\Exception $e) {
                    $this->logger->error("An exception was throw importing vehicle with model_year: "
                        . $rowData[self::MODEL_YEAR] . " and model_code: " . $rowData[self::MODEL_CODE]);
                    $this->logger->error($e);
                    $this->addRowError($e->getMessage(), $rowNum);
                }
            }

            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->saveVehicleFinish($entityCreateList, $entityUpdateList, self::VEHICLE_TABLE);
            }
        }

        $this->logger->info("Vehicles Created : " . $this->countItemsCreated . ", Updated : " . $this->countItemsUpdated);

        return $this;
    }

    /**
     * Removes empty attribute constant from imported data
     *
     * @param array $rowData
     * @return array
     */
    protected function removeEmptyAttributeConstant(array $rowData): array
    {
        if (
            array_key_exists(self::TITLE, $rowData)
            && strtolower($rowData[self::TITLE]) == strtolower($this->getEmptyAttributeValueConstant())
        ) {
            $rowData[self::TITLE] = '';
        }
        if (
            array_key_exists(self::BODY_STYLE, $rowData)
            && strtolower($rowData[self::BODY_STYLE]) == strtolower($this->getEmptyAttributeValueConstant())
        ) {
            $rowData[self::BODY_STYLE] = '';
        }
        if (
            array_key_exists(self::ENGINE_TYPE, $rowData)
            && strtolower($rowData[self::ENGINE_TYPE]) == strtolower($this->getEmptyAttributeValueConstant())
        ) {
            $rowData[self::ENGINE_TYPE] = '';
        }
        if (
            array_key_exists(self::MODEL_RANGE, $rowData)
            && strtolower($rowData[self::MODEL_RANGE]) == strtolower($this->getEmptyAttributeValueConstant())
        ) {
            $rowData[self::MODEL_RANGE] = '';
        }
        if (
            array_key_exists(self::MODEL_DESCRIPTION, $rowData)
            && strtolower($rowData[self::MODEL_DESCRIPTION]) == strtolower($this->getEmptyAttributeValueConstant())
        ) {
            $rowData[self::MODEL_DESCRIPTION] = '';
        }
        if (
            array_key_exists(self::TRANSMISSION, $rowData)
            && strtolower($rowData[self::TRANSMISSION]) == strtolower($this->getEmptyAttributeValueConstant())
        ) {
            $rowData[self::TRANSMISSION] = '';
        }

        return $rowData;
    }

    /**
     * Format incoming Vehicle Data
     *
     * @param array $vehicleData
     * @return string
     */
    protected function formatVehicleData($vehicleData)
    {
        //Combine Grade and Body Style
        $vehicleData[self::GRADE] = str_replace("  ", " ", trim($vehicleData[self::GRADE] . " " .
            $vehicleData[self::BODY_STYLE]));

        //Combine Driveline, Engine Type and Transmission
        $vehicleData[self::DRIVELINE] = str_replace("  ", " ", trim($vehicleData[self::DRIVELINE] . " " .
            $vehicleData[self::ENGINE_TYPE] . " " .
            $vehicleData[self::TRANSMISSION]));

        return $vehicleData;
    }

    /**
     * Check validation for new vehicle creation
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function validateNewVehicle($rowData, $rowNum)
    {
        if (!isset($rowData[self::BRAND]) || empty($rowData[self::BRAND])) {
            $this->addRowError(ValidatorInterface::ERROR_BRAND_IS_EMPTY, $rowNum);
            return false;
        }

        if (!isset($rowData[self::SERIES_NAME]) || empty($rowData[self::SERIES_NAME])) {
            $this->addRowError(ValidatorInterface::ERROR_SERIES_NAME_IS_EMPTY, $rowNum);
            return false;
        }
        if (!isset($rowData[self::GRADE]) || empty($rowData[self::GRADE])) {
            $this->addRowError(ValidatorInterface::ERROR_GRADE_IS_EMPTY, $rowNum);
            return false;
        }
        if (!isset($rowData[self::DRIVELINE]) || empty($rowData[self::DRIVELINE])) {
            $this->addRowError(ValidatorInterface::ERROR_DRIVELINE_IS_EMPTY, $rowNum);
            return false;
        }
        return true;
    }
    /**
     * Check validation for update vehicle
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function validateUpdateVehicle($rowData, $rowNum)
    {
        if (array_key_exists(self::BRAND, $rowData) && (empty($rowData[self::BRAND])
            || is_null($rowData[self::BRAND]))) {
            $this->addRowError(ValidatorInterface::ERROR_BRAND_IS_EMPTY, $rowNum);
            return false;
        }

        if (
            array_key_exists(self::SERIES_NAME, $rowData) && empty($rowData[self::SERIES_NAME])
            || is_null($rowData[self::SERIES_NAME])
        ) {
            $this->addRowError(ValidatorInterface::ERROR_SERIES_NAME_IS_EMPTY, $rowNum);
            return false;
        }
        if (array_key_exists(self::GRADE, $rowData) && (empty($rowData[self::GRADE])
            || is_null($rowData[self::GRADE]))) {
            $this->addRowError(ValidatorInterface::ERROR_GRADE_IS_EMPTY, $rowNum);
            return false;
        }
        if (
            array_key_exists(self::DRIVELINE, $rowData) && empty($rowData[self::DRIVELINE])
            || is_null($rowData[self::DRIVELINE])
        ) {
            $this->addRowError(ValidatorInterface::ERROR_DRIVELINE_IS_EMPTY, $rowNum);
            return false;
        }
        return true;
    }
    /**
     * Update Row values
     *
     * @param array $rowData
     * @param array $vehicleData
     * @return array
     */
    protected function updateVehicleRow($rowData, $vehicleData)
    {
        if (array_key_exists(self::TITLE, $rowData)) {
            if (strtolower($rowData[self::TITLE]) == strtolower($this->getEmptyAttributeValueConstant())) {
                $rowData[self::TITLE] = '';
            } else {
                if (empty($rowData[self::TITLE]) || is_null($rowData[self::TITLE])) {
                    $rowData[self::TITLE] = $vehicleData[self::TITLE];
                }
            }
        }
        if (array_key_exists(self::BODY_STYLE, $rowData)) {
            if (strtolower($rowData[self::BODY_STYLE]) == strtolower($this->getEmptyAttributeValueConstant())) {
                $rowData[self::BODY_STYLE] = '';
            } else {
                if (empty($rowData[self::BODY_STYLE]) || is_null($rowData[self::BODY_STYLE])) {
                    $rowData[self::BODY_STYLE] = $vehicleData[self::BODY_STYLE];
                }
            }
        }
        if (array_key_exists(self::ENGINE_TYPE, $rowData)) {
            if (strtolower($rowData[self::ENGINE_TYPE]) == strtolower($this->getEmptyAttributeValueConstant())) {
                $rowData[self::ENGINE_TYPE] = '';
            } else {
                if (empty($rowData[self::ENGINE_TYPE]) || is_null($rowData[self::ENGINE_TYPE])) {
                    $rowData[self::ENGINE_TYPE] = $vehicleData[self::ENGINE_TYPE];
                }
            }
        }
        if (array_key_exists(self::MODEL_RANGE, $rowData)) {
            if (strtolower($rowData[self::MODEL_RANGE]) == strtolower($this->getEmptyAttributeValueConstant())) {
                $rowData[self::MODEL_RANGE] = '';
            } else {
                if (empty($rowData[self::MODEL_RANGE]) || is_null($rowData[self::MODEL_RANGE])) {
                    $rowData[self::MODEL_RANGE] = $vehicleData[self::MODEL_RANGE];
                }
            }
        }
        if (array_key_exists(self::MODEL_DESCRIPTION, $rowData)) {
            if (strtolower($rowData[self::MODEL_DESCRIPTION]) == strtolower($this->getEmptyAttributeValueConstant())) {
                $rowData[self::MODEL_DESCRIPTION] = '';
            } else {
                if (empty($rowData[self::MODEL_DESCRIPTION]) || is_null($rowData[self::MODEL_DESCRIPTION])) {
                    $rowData[self::MODEL_DESCRIPTION] = $vehicleData[self::MODEL_DESCRIPTION];
                }
            }
        }
        if (array_key_exists(self::TRANSMISSION, $rowData)) {
            if (strtolower($rowData[self::TRANSMISSION]) == strtolower($this->getEmptyAttributeValueConstant())) {
                $rowData[self::TRANSMISSION] = '';
            } else {
                if (empty($rowData[self::TRANSMISSION]) || is_null($rowData[self::TRANSMISSION])) {
                    $rowData[self::TRANSMISSION] = $vehicleData[self::TRANSMISSION];
                }
            }
        }
        return $rowData;
    }

    /**
     * Save vehicle data.
     *
     * @param array $entityCreateList
     * @param array $entityUpdateList
     * @param string $table
     * @return $this
     */
    protected function saveVehicleFinish(array $entityCreateList, array $entityUpdateList, $table)
    {
        $this->countItemsCreated += count($entityCreateList);

        $tableName = $this->_connection->getTableName($table);

        //update existing vehicles
        if ($entityUpdateList) {
            $entityIn = [];
            foreach ($entityUpdateList as $id => $entityRows) {
                $this->countItemsUpdated += count($entityRows);

                foreach ($entityRows as $row) {
                    $entityIn[] = $row;
                }
            }
            if ($entityIn) {
                $this->_connection->insertOnDuplicate($tableName, $entityIn);
            }
        }

        //create new vehicles
        if ($entityCreateList) {
            $this->_connection->insertMultiple($tableName, $entityCreateList);
        }

        return $this;
    }

    /**
     * Delete vehicle data.
     *
     * @param array $listVehicle
     * @param string $table
     * @return bool
     */
    protected function deleteVehicleFinish(array $listVehicle, $table)
    {
        $listVehicle = implode(',', $listVehicle);
        $listVehicle = "(" . $listVehicle . ")";

        if ($table && $listVehicle) {
            try {
                //fetch mapped product ids
                $select = $this->_connection->select()
                    ->from(
                        ['main_table' => self::VEHICLE_TABLE],
                        [
                            'mapping_table.product_id'
                        ]
                    )
                    ->join(
                        ['mapping_table' => VehicleProductMappingInterface::VEHICLE_PRODUCT_MAPPING_TABLE],
                        'main_table.entity_id = mapping_table.vehicle_id'
                    )
                    ->where("(main_table.model_year, main_table.model_code) IN " . $listVehicle);
                $mappedProducts = $this->_connection->fetchCol($select);

                //add product indexes
                if ($mappedProducts) {
                    $mappedProducts = array_unique($mappedProducts);
                    foreach ($mappedProducts as $product) {
                        $bulkInsert[] = [
                            'entity_id' => $product
                        ];
                    }
                    $this->_connection->insertMultiple('catalogsearch_fulltext_cl', $bulkInsert);
                }

                //delete vehicles data
                $this->countItemsDeleted += $this->_connection->delete(
                    $this->_connection->getTableName($table),
                    ["(model_year, model_code) IN $listVehicle"]
                );

                return true;
            } catch (\Exception $e) {
                $this->logger->error("Exception Deleting Vehicle");
                $this->logger->error($e);
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * Get Vehicle Data
     *
     * @param array $rowData
     * @return array
     */
    protected function getVehicleDetails($rowData)
    {
        $select = $this->_connection->select()->from(
            self::VEHICLE_TABLE
        )->where('model_year= (?)', $rowData[self::MODEL_YEAR])
            ->where('model_code= (?)', $rowData[self::MODEL_CODE]);
        $vehicleData = $this->_connection->fetchRow($select);

        return $vehicleData;
    }

    /**
     * Get Existing Vehicle
     *
     * @param array $rowData
     * @return array
     */
    protected function getExistingVehicle($rowData)
    {
        $select = $this->_connection->select()->from(
            self::VEHICLE_TABLE
        )->where('model_year= (?)', $rowData[self::MODEL_YEAR])
            ->where('series_name= (?)', $rowData[self::SERIES_NAME])
            ->where('grade= (?)', $rowData[self::GRADE])
            ->where('driveline= (?)', $rowData[self::DRIVELINE]);
        $vehicleData = $this->_connection->fetchRow($select);
        return $vehicleData;
    }


    /**
     * Get Existing Vehicle replace data
     */
    protected function getVehicleReplaceData(): array
    {
        $select = $this->_connection->select()->from(
            self::VEHICLE_REPLACE_TABLE
        );
        $vehicleReplaceData = $this->_connection->fetchAll($select);

        if (!$vehicleReplaceData) {
            $vehicleReplaceData = [];
        }

        return $vehicleReplaceData;
    }

    /**
     * Return empty attribute value constant
     *
     * @return string
     * @since 101.0.0
     */
    public function getEmptyAttributeValueConstant()
    {
        if (!empty($this->_parameters[Import::FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT])) {
            return $this->_parameters[Import::FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT];
        }
        return self::DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT;
    }
}
