<?php

/**
 * @package   Infosys/CustomerSSO
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerSSO\Api;

interface DCSInterface
{
    /**
     * Get API token based on resource
     *
     * @param string $customerData
     * @return string
     */
    public function getCustomerToken($customerData);

    /**
     * API to update customer email SSO
     *
     * @param string $customerData
     * @return array
     */
    public function updateCustomerEmail($customerData);

    /**
     * API to update customer details SSO
     *
     * @param string $customerData
     * @return array
     */
    public function updateCustomerDetails($customerData);

    /**
     * API to activate customer SSO
     *
     * @param string $customerData
     * @return array
     */
    public function activateCustomerEmail($customerData);

    /**
     * API to get customer SSO
     *
     * @param string $idToken
     * @return array
     */
    public function getCustomerEmail($idToken);
}
