<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerCentral\Api;

interface CustomerCentralInterface
{
    /**
     * Get API token based on resource
     *
     * @param string $resource
     * @return string
     */
    public function getToken($resource);
    /**
     * API to save customer info in customer central
     *
     * @param string $customerInfo
     * @return array
     */
    public function saveCustomerDetails($customerInfo);
    /**
     * Save order data to customer central
     *
     * @param array $requestData
     * @return void
     */
    public function partsOnlinePurchase($requestData);
}
