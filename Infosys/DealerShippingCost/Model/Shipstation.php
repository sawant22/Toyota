<?php

/**
 * @package   Infosys/DealerShippingCost
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\DealerShippingCost\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Infosys\DealerShippingCost\Api\ShipstationInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Encryption\EncryptorInterface;
use Infosys\DealerShippingCost\Logger\ShippingCostLogger;

/**
 * Class to call shipstation apis
 */
class Shipstation implements ShipstationInterface
{
    const CURL_STATUS = 200;

    protected Curl $curl;

    protected ScopeConfigInterface $scopeConfig;

    protected Json $json;

    protected EncryptorInterface $encryptor;

    protected ShippingCostLogger $logger;

    /**
     * Constructor function
     *
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param EncryptorInterface $encryptor
     * @param ShippingCostLogger $logger
     */
    public function __construct(
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        EncryptorInterface $encryptor,
        ShippingCostLogger $logger
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }

    /**
     * API to get shipments list
     *
     * @param string $orderNumber
     * @param int $storeId
     * @return array
     */
    public function listShipments($orderNumber, $storeId): array
    {
        $responseData = [];
        $api_key = $this->getConfig('shipstation_general/integration_settings/api_key', $storeId);
        $api_secret = $this->getConfig('shipstation_general/integration_settings/api_secret', $storeId);
        $decrypt_api_secret = $this->encryptor->decrypt($api_secret);
        $host = $this->getConfig('shipstation_general/integration_settings/api_url');
        $connectionTimeout = $this->getConfig('shipstation_general/shipstation_api_timeout/connection_timeout');
        $requestTimeout = $this->getConfig('shipstation_general/shipstation_api_timeout/request_timeout');

        $requestData = [
            'orderNumber' => $orderNumber,
        ];

        $apiUrl = $host . '/shipments?' . http_build_query($requestData);

        $authentication = base64_encode("{$api_key}:{$decrypt_api_secret}");
        $apiRequest = "curl --location --request GET 'https://ssapi.shipstation.com/shipments?orderNumber=$orderNumber \
                    --header 'Authorization: Basic $authentication";
        $this->logger->info("API request" . $apiRequest);

        $options =  [
            CURLOPT_CONNECTTIMEOUT => $connectionTimeout
        ];

        try {
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->setCredentials($api_key, $decrypt_api_secret);
            $this->curl->setOptions($options);
            $this->curl->setTimeout($requestTimeout);
            $this->curl->get($apiUrl);

            //response will contain the output of curl request
            $response = $this->curl->getBody();

            //non 200 http response
            $httpStatusCode = $this->curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $this->logger->error('Shipstation API request is failing. HTTP status code: ' . $httpStatusCode);
                $this->logger->error('Shipstation API response' . $response);
            }

            if ($response) {
                $responseData = $this->json->unserialize($response);
                $this->logger->info("Shipstation api response " . print_r($responseData, true));
            }
        } catch (\Exception $e) {
            $this->logger->error('List shipments API not working' . $e);
        }

        return $responseData;
    }

    /**
     * Get store config value for shipstation API
     *
     * @param string $path
     * @param int $storeId
     * @return string
     */
    protected function getConfig($path, $storeId = null): string
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
