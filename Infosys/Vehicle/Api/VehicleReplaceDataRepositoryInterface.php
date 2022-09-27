<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Api;

/**
 * @api
 * @since 100.0.2
 */
interface VehicleReplaceDataRepositoryInterface
{
    /**
     * Create vehicle replace data
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface $vehicleReplaceData
     * @return \Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface $vehicleReplaceData);

    /**
     * Get info about vehicle replace data by vehicle replace data id
     *
     * @param int $vehicleReplaceDataId
     * @return \Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($vehicleReplaceDataId);

    /**
     * Delete vehicle replace data
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleInterface $vehicle
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Infosys\Vehicle\Api\Data\VehicleReplaceDataInterface $vehicleReplaceData);

    /**
     * Get vehicle replace data list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
