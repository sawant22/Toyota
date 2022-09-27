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
interface VehicleProductMappingRepositoryInterface
{
    /**
     * Create vehicle product mapping
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleProductMappingInterface $vehicleProductMapping
     * @return \Infosys\Vehicle\Api\Data\VehicleProductMappingInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Infosys\Vehicle\Api\Data\VehicleProductMappingInterface $vehicleProductMapping);

    /**
     * Get info about vehicle by vehicle id
     *
     * @param int $vehicleProductMappingId
     * @return \Infosys\Vehicle\Api\Data\VehicleProductMappingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($vehicleProductMappingId);

    /**
     * Delete vehicle
     *
     * @param \Infosys\Vehicle\Api\Data\VehicleProductMappingInterface $vehicleProductMappingId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Infosys\Vehicle\Api\Data\VehicleProductMappingInterface $vehicleProductMappingId);

    /**
     * Get vehicle list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
