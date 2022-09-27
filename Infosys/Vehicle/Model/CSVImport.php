<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model;

use Infosys\Vehicle\Model\VehicleReplaceDataFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\File\Csv;
use Infosys\Vehicle\Logger\VehicleLogger;

/**
 * Vehicle data replace CSV Import Handler
 *
 */
class CSVImport
{
    const ENTITY_ID_COLUMN = 'entity_id';
    const VEHICLE_TABLE = 'catalog_vehicle_entity';
    const ATTRIBUTE = 'attribute';
    const FIND = 'find';
    const REPLACE = 'replace';
    protected VehicleReplaceDataFactory $vehicleReplaceDataFactory;
    protected Csv $csvProcessor;
    protected ResourceConnection $resource;
    protected $connection;
    protected VehicleLogger $logger;

    /**
     * Valid columns
     *
     * @var array
     */
    protected $validColumnNames = [
        self::ATTRIBUTE,
        self::FIND,
        self::REPLACE
    ];

    
        /**
     * Valid attribute keys
     *
     * @var array
     */
    const VALID_FIND_REPLACE_ATTRIBUTE_KEYS = [
        Vehicle::SERIES_NAME,
        Vehicle::GRADE,
        Vehicle::DRIVELINE,
        Vehicle::BODY_STYLE,
        Vehicle::ENGINE_TYPE,
        Vehicle::TRANSMISSION
    ];


    /**
     * @param VehicleReplaceDataFactory $vehicleReplaceDataFactory
     * @param Csv $csvProcessor
     * @param ResourceConnection $resource
     * @param VehicleLogger $logger
     */
    public function __construct(
        VehicleReplaceDataFactory $vehicleReplaceDataFactory,
        Csv $csvProcessor,
        ResourceConnection $resource,
        VehicleLogger $logger
    ) {
        $this->vehicleReplaceDataFactory = $vehicleReplaceDataFactory;
        $this->csvProcessor = $csvProcessor;
        $this->resource = $resource;
        $this->connection  = $resource->getConnection();
        $this->logger = $logger;
    }

    /**
     * Retrieve a list of fields required for CSV file (order is important!)
     *
     * @return array
     */
    public function getRequiredCsvFields()
    {
        // indexes are specified for clarity, they are used during import
        return [
            0 => __('Attribute'),
            1 => __('Find'),
            2 => __('Replace')
        ];
    }

    /**
     * Import Vehicle Replace Data from CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $vehicleReplaceDataRawData = $this->csvProcessor->getData($file['tmp_name']);
        // first row of file represents headers
        $fileFields = $vehicleReplaceDataRawData[0];
        $validFields = $this->_filterFileFields($fileFields);
        $invalidFields = array_diff_key($fileFields, $validFields);
        $vehicleReplaceDataData = $this->_filterVehicleData($vehicleReplaceDataRawData, $invalidFields, $validFields);

        $columns = [];
        foreach ($vehicleReplaceDataData as $rowIndex => $dataRow) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }

            $vehicleAttributeData = $this->_importVehicleReplaceData($dataRow);   

            foreach ($this->getValidColumnNames() as $columnKey) {
                if(!in_array($vehicleAttributeData[self::ATTRIBUTE], self::VALID_FIND_REPLACE_ATTRIBUTE_KEYS)){
                    $this->logger->info("Invalid vehicle attribute : row - " .$rowIndex. " | Attribute Name - " . $vehicleAttributeData['attribute']);
                    break;
                }
                $columns[$rowIndex][$columnKey] = $vehicleAttributeData[$columnKey];
            }
        }
        try {
            //Truncate the vehicle replace data table records
            $tableName = $this->resource->getTableName("vehicle_data_replace");            
            if(count($columns)){
                $this->connection->truncateTable($tableName);
            }        
            //Insert the vehicle replace data into table
            return $this->connection->insertMultiple($tableName, $columns);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __("Please check the log. can't insert the data."));
        }
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields(array $fileFields)
    {
        $filteredFields = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $fileFieldsNum = count($fileFields);

        if ($fileFieldsNum != $requiredFieldsNum) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid column is available in upload repalce data CSV import. Please check and update the file'));
        }

        return $filteredFields;
    }

    /**
     * Filter vehicleReplaceData data (i.e. unset all invalid fields and check consistency)
     *
     * @param array $vehicleRawData
     * @param array $invalidFields assoc array of invalid file fields
     * @param array $validFields assoc array of valid file fields
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _filterVehicleData(array $vehicleRawData, array $invalidFields, array $validFields) : array
    {
        $validFieldsNum = count($validFields);
        foreach ($vehicleRawData as $rowIndex => $dataRow) {
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($vehicleRawData[$rowIndex]);
                continue;
            }
            // unset invalid fields from data row
            foreach ($dataRow as $fieldIndex => $fieldValue) {            
                if (isset($invalidFields[$fieldIndex])) {
                    unset($vehicleRawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
            if (count($vehicleRawData[$rowIndex]) != $validFieldsNum) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format.'));
            }
        }
        return $vehicleRawData;
    }

    /**
     * Import single vehicle replace data
     *
     * @param array $vehicleData
     * @return array
     */
    protected function _importVehicleReplaceData(array $vehicleData) : array
    {
        $modelData = [
            self::ATTRIBUTE => $vehicleData[0],
            self::FIND => $vehicleData[1],
            self::REPLACE => $vehicleData[2]
        ];

        return $modelData;
    }

    /**
     * Valid column names
     *
     * @return array
     */
    public function getValidColumnNames() : array
    {
        return $this->validColumnNames;
    }

}
