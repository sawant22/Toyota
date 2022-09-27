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
interface VehicleRepositoryInterface
{
    /**
     * Create vehicle
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleInterface $vehicle
     * @return \Infosys\Vehicle\Api\Data\VehicleInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Infosys\Vehicle\Api\Data\VehicleInterface $vehicle);

    /**
     * Get info about vehicle by vehicle id
     *
     * @param int $vehicleId
     * @return \Infosys\Vehicle\Api\Data\VehicleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($vehicleId);

    /**
     * Delete vehicle
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleInterface $vehicle
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Infosys\Vehicle\Api\Data\VehicleInterface $vehicle);

    /**
     * Get vehicle list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
