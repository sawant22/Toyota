<?php

/**
 * @package   Infosys/YMMSearchGraphQL
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\YMMSearchGraphQL\Model;

use Magento\Framework\HTTP\Client\Curl;
use Infosys\YMMSearchGraphQL\Api\EFCInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManager;
use Infosys\YMMSearchGraphQL\Logger\EFCLogger;

class EFCapi implements EFCInterface
{
    const CURL_STATUS = 200;
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var EFCLogger
     */
    protected $efcLogger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * Constructor function
     *
     * @param Curl $curl
     * @param Json $json
     * @param EFCLogger $efcLogger
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManager $storeManager
     */
    public function __construct(
        Curl $curl,
        Json $json,
        EFCLogger $efcLogger,
        ScopeConfigInterface $scopeConfig,
        StoreManager $storeManager
    ) {
        $this->_curl = $curl;
        $this->_json = $json;
        $this->efcLogger = $efcLogger;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * API to get vehicle image
     *
     * @param string $year
     * @param string $trim
     * @return array
     */
    public function getVehicleImage($year, $trim)
    {
        $responseData = [];
        $storeId = $this->storeManager->getStore()->getId();
        $apikey = $this->getConfig('searchbyYMM/general/apikey');
        $apiurl = $this->getConfig('searchbyYMM/general/apiurl');
        $imageurl = $this->getConfig('searchbyYMM/general/imageurl');
        $image_brand = $this->getConfig('searchbyYMM/general/image_brand', $storeId);
        $connectionTimeout = $this->getConfig('searchbyYMM/efc_api_timeout/efc_connection_timeout');
        $requestTimeout = $this->getConfig('searchbyYMM/efc_api_timeout/efc_request_timeout');
        $agent = 'PCO Adobe Commerce';
        $url = $apiurl . "/vehiclecontent/v2/" . $image_brand . "/NATIONAL/EN/trims?year=" . $year . "&trim=" . $trim;
        try {
            $options = [
                CURLOPT_USERAGENT => $agent,
                CURLOPT_HTTPHEADER => ["x-api-key: " . $apikey],
                CURLOPT_CONNECTTIMEOUT => $connectionTimeout
            ];
            $this->_curl->setOptions($options);
            $this->_curl->setTimeout($requestTimeout);
            $this->_curl->get($url);
            $response = $this->_curl->getBody();

            //non 200 http response
            $httpStatusCode = $this->_curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $this->efcLogger->error('EFC API request is failing.HTTP status code: ' . $httpStatusCode);
                $this->efcLogger->error('EFC API response' . $response);
            }

            if ($response) {
                $this->efcLogger->info("EFC API response: " . $response);
                $responseData = $this->getJsonDecode($response);
                $responseData['imageurl'] = $imageurl;
            }
        } catch (\Exception $e) {
            $this->efcLogger->error('EFC API not working' . $e->getMessage());
        }
        return $responseData;
    }

    /**
     * Json data
     *
     * @param array $response
     * @return array
     */
    public function getJsonDecode($response)
    {
        return $this->_json->unserialize($response);
    }

    /**
     * Get store config value for EFC API
     *
     * @param string $path
     * @param int $storeId
     * @return void
     */
    protected function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
