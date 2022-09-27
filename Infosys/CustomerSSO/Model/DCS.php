<?php

/**
 * @package   Infosys/CustomerSSO
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerSSO\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Infosys\CustomerSSO\Api\DCSInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Infosys\CustomerSSO\Logger\DCSLogger;
use Infosys\CustomerSSO\Helper\Data;

class DCS implements DCSInterface
{
    /**
     * Curl Status for 200
     */
    const CURL_STATUS = 200;

    protected Curl $curl;

    protected ScopeConfigInterface $scopeConfig;

    protected Json $json;

    protected CustomerRepositoryInterface $customerRepository;

    protected LoggerInterface $logger;

    protected CurlFactory $curlFactory;

    protected SessionManagerInterface $session;

    protected DCSLogger $dcsLogger;

    protected Data $dcsHelper;

    /**
     * Constructor function
     *
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param DCSLogger $dcsLogger
     * @param CurlFactory $curlFactory
     * @param SessionManagerInterface $session
     * @param Data $dcsHelper
     */
    public function __construct(
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        DCSLogger $dcsLogger,
        CurlFactory $curlFactory,
        SessionManagerInterface $session,
        Data $dcsHelper
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->dcsLogger = $dcsLogger;
        $this->curlFactory = $curlFactory;
        $this->session = $session;
        $this->dcsHelper = $dcsHelper;
    }

    /**
     * Get API token based on resource
     *
     * @param string $customerData
     */
    public function getCustomerToken($customerData): string
    {
        $response = '';
        $tokenUrl = $this->getConfig('dcs/token_api/token_url');
        $grantType = $this->getConfig('dcs/token_api/grant_type');
        $clientId = $this->getConfig('dcs/token_api/client_id');
        $clientSecret = $this->getConfig('dcs/token_api/client_secret');
        $scope = $this->getConfig('dcs/token_api/scope');
        $connectionTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_connection_timeout');
        $requestTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_request_timeout');
        $params = [
            'grant_type' => $grantType,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
            'username' => $customerData['email'],
            'password' => $customerData['password']
        ];
        $this->curl->addHeader("Content-Type", "application/x-www-form-urlencoded");
        $options =  [CURLOPT_CONNECTTIMEOUT => $connectionTimeout];
        $this->curl->setOptions($options);
        $this->curl->setTimeout($requestTimeout);
        try {
            $this->curl->post($tokenUrl, $params);
             //response will contain the output of curl request
            $response = $this->curl->getBody();
            $httpStatusCode = $this->curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $this->dcsLogger->error('Token API request: ' . json_encode($params));
                $this->dcsLogger->error('Token API response: ' . json_encode($response));
                $this->dcsLogger->error('Token Url: ' . json_encode($tokenUrl));
            } else {
                if ($this->dcsHelper->isLogEnabled()) {
                    $this->dcsLogger->info('Token API response: ' . json_encode($response));
                }
                if ($response) {
                    $response = $this->json->unserialize($response);
                    if (isset($response['id_token'])) {
                        $response = $response['id_token'];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->dcsLogger->error('Token API not working' . $e->getMessage());
        }
         return $response;
    }

    /**
     * API to update customer email in SSO
     *
     * @param string $customerData
     * @return string
     */
    public function updateCustomerEmail($customerData): string
    {
        $response = '';
        $customerInfo = ['email' => $customerData['new_email'], 'currentPassword' =>$customerData['password']];
        $customerInfo = $this->json->serialize($customerInfo);
        $token = $this->getCustomerToken($customerData);
        $url = $this->getConfig('dcs/update_customer/update_customer_api_url');
        $x_client = $this->getConfig('dcs/update_customer/x_client');
        $x_brand =  $this->getConfig('dcs/update_customer/x_brand');
        $x_version =  $this->getConfig('dcs/update_customer/x_version');
        $connectionTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_connection_timeout');
        $requestTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_request_timeout');

        try {
            $requstbody = $customerInfo;
            $headers = [
                'Content-Type: application/json',
                'X-VERSION: '.$x_version,
                'Authorization: '.$token,
                'X-BRAND: '.$x_brand,
                'X-CLIENT: '.$x_client
            ];
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("X-BRAND", $x_brand);
            $this->curl->addHeader("X-CLIENT", $x_client);
            $this->curl->addHeader("X-VERSION", $x_version);
            $this->curl->addHeader("Authorization", $token);
            $options =  [CURLOPT_CONNECTTIMEOUT => $connectionTimeout, CURLOPT_CUSTOMREQUEST=> "PUT"];
            $this->curl->setOptions($options);
            $this->curl->setTimeout($requestTimeout);
            $this->curl->post($url, $requstbody);
            $response = $this->curl->getBody();
            $httpStatusCode = $this->curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $this->dcsLogger->info('Update customer email request: ' . json_encode($requstbody));
                $this->dcsLogger->error('Update customer email  API response: ' . json_encode($response));
                $this->dcsLogger->error('Update customer email  Url: ' .json_encode($url));
            } else {
                if ($this->dcsHelper->isLogEnabled()) {
                    $request_copy = json_decode($requstbody, true);
                    unset($request_copy["currentPassword"]);
                    $update_email_request = json_encode($request_copy);
                    $this->dcsLogger->info('Update customer email request: ' . json_encode($requstbody));
                    $this->dcsLogger->info('Update customer email response: ' . json_encode($response));
                }
                if ($response) {
                    $response = $this->json->unserialize($response);
                }
            }
        } catch (\Exception $e) {
            $this->dcsLogger->error('Update customer email API not working' . $e->getMessage());
        }
        return $response;
    }

    /**
     * API to update customer info in SSO
     *
     * @param string $customerData
     * @return string
     */
    public function updateCustomerDetails($customerData): string
    {
        $response = '';
        $customerInfo = ['email' => $customerData['email'], 'password' =>$customerData['currentPassword']];
        $token = $this->getCustomerToken($customerInfo);
        $this->session->setToken($token);
        $this->session->setPhone($customerData['areaCode'].$customerData['phoneNumber']);
        $customerData = $this->json->serialize($customerData);

        $url = $this->getConfig('dcs/update_customer/update_customer_api_url');
        $x_client = $this->getConfig('dcs/update_customer/x_client');
        $x_brand =  $this->getConfig('dcs/update_customer/x_brand');
        $x_version =  $this->getConfig('dcs/update_customer/x_version');
        $connectionTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_connection_timeout');
        $requestTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_request_timeout');
        try {
            $requstbody = $customerData;
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("X-BRAND", $x_brand);
            $this->curl->addHeader("X-CLIENT", $x_client);
            $this->curl->addHeader("X-VERSION", $x_version);
            $this->curl->addHeader("Authorization", $token);
            $options =  [CURLOPT_CONNECTTIMEOUT => $connectionTimeout, CURLOPT_CUSTOMREQUEST=> "PUT"];
            $this->curl->setOptions($options);
            $this->curl->setTimeout($requestTimeout);
            $this->curl->post($url, $requstbody);
            $response = $this->curl->getBody();
            $httpStatusCode = $this->curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $this->dcsLogger->error('Update customer details API request: ' . json_encode($requstbody));
                $this->dcsLogger->error('Update customer details API response: ' . json_encode($response));
                $this->dcsLogger->error('Update customer details  Url: ' . $url);
            } else {
                if ($this->dcsHelper->isLogEnabled()) {
                    $request_copy = json_decode($requstbody, true);
                    unset($request_copy["currentPassword"]);
                    $update_customer_request = json_encode($request_copy);
                    $this->dcsLogger->info('Update customer details API request: ' . json_encode($update_customer_request));
                    $this->dcsLogger->info('Update customer details API response: ' . json_encode($response));
                }
                if ($response) {
                    $response = $this->json->unserialize($response);
                }
            }
        } catch (\Exception $e) {
            $this->dcsLogger->error('Update customer detials API not working' . $e->getMessage());
        }
        
        return $response;
    }

    /**
     * API to verify customer phone with otp
     *
     * @param string $otpCode
     * @return string
     */
    public function verifyCustomerPhone($otpCode): string
    {
        $response ='';
        if ($this->dcsHelper->isLogEnabled()) {
            $this->dcsLogger->info('Validate customer phone API call');
        }
        $token = $this->session->getToken();
        $phone = $this->session->getPhone();
        $customerData = [
            'otpCode'=> $otpCode,
            'phoneNumber' => $phone
        ];
        $customerData = $this->json->serialize($customerData);
        $url = $this->getConfig('dcs/verify_phone/phone_otp_api_url');
        $x_client = $this->getConfig('dcs/verify_phone/client_get_v');
        $x_brand =  $this->getConfig('dcs/verify_phone/brand_get_v');
        $x_version =  $this->getConfig('dcs/verify_phone/version_get_v');
        $connectionTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_connection_timeout');
        $requestTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_request_timeout');

        try {
            $requstbody = $customerData;
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("X-BRAND", $x_brand);
            $this->curl->addHeader("X-CLIENT", $x_client);
            $this->curl->addHeader("X-VERSION", $x_version);
            $this->curl->addHeader("Authorization", $token);
            $options =  [CURLOPT_CONNECTTIMEOUT => $connectionTimeout, CURLOPT_CUSTOMREQUEST=> "PUT"];
            $this->curl->setOptions($options);
            $this->curl->setTimeout($requestTimeout);
            $this->curl->post($url, $requstbody);
            //response will contain the output of curl request
            $response = $this->curl->getBody();
            $httpStatusCode = $this->curl->getStatus();

            if ($httpStatusCode != self::CURL_STATUS) {
                $this->dcsLogger->error('verify customer phone API request: ' . json_encode($requstbody));
                $this->dcsLogger->error('verify customer phone API response: ' . json_encode($response));
                $this->dcsLogger->error('verify customer phone  Url: ' . json_encode($url));
            } else {
                if ($this->dcsHelper->isLogEnabled()) {
                    $this->dcsLogger->info('Verify phone API request: ' . json_encode($requstbody));
                    $this->dcsLogger->info('Verify phone API response: ' .json_encode($response));
                }
                if ($response) {
                    $response = $this->json->unserialize($response);
                }
            }
        } catch (\Exception $e) {
            $this->dcsLogger->error('Verify phone API not working' . $e->getMessage());
        }
        $this->session->unsToken();
        return $response;
    }
    /**
     * API to activate customer email
     *
     * @param string $activationCode
     * @return string
     */
    public function activateCustomerEmail($activationCode): string
    {
        $response = '';
        $params = ['activationCode' => $activationCode];
        $requstbody = $this->json->serialize($params);

        $url = $this->getConfig('dcs/activate_customer/activate_customer_api_url');
        $x_client = $this->getConfig('dcs/activate_customer/x_client_active');
        $x_brand =  $this->getConfig('dcs/activate_customer/x_brand_active');
        $x_version =  $this->getConfig('dcs/activate_customer/x_version_active');
        $connectionTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_connection_timeout');
        $requestTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_request_timeout');
        try {
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("X-BRAND", $x_brand);
            $this->curl->addHeader("X-CLIENT", $x_client);
            $this->curl->addHeader("X-VERSION", $x_version);
            $options =  [CURLOPT_CONNECTTIMEOUT => $connectionTimeout];
            $this->curl->setOptions($options);
            $this->curl->setTimeout($requestTimeout);
            $this->curl->patch($url, $requstbody);
            //response will contain the output of curl request
            $response = $this->curl->getBody();
            $httpStatusCode = $this->curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $this->dcsLogger->error('Activate customer email API request: ' . json_encode($requstbody));
                $this->dcsLogger->error('Activate customer email API response: ' . json_encode($response));
                $this->dcsLogger->error('Activate customer email Url: ' . $url);
            } else {
                if ($this->dcsHelper->isLogEnabled()) {
                    $this->dcsLogger->info('Activate customer email  API request' . json_encode($requstbody));
                    $this->dcsLogger->info('Activate customer email  API response' . json_encode($response));
                }
                if ($response) {
                    $response = $this->json->unserialize($response);
                }
            }
        } catch (\Exception $e) {
            $this->dcsLogger->error('Activate customer email API not working' . $e->getMessage());
        }
        return $response;
    }

     /**
      * API to get customer email
      *
      * @param string $idToken
      * @return string
      */
    public function getCustomerEmail($idToken): string
    {
        $response = '';
        $url = $this->getConfig('dcs/get_customer/get_customer_api_url');
        $x_client = $this->getConfig('dcs/get_customer/x_client_get');
        $x_brand =  $this->getConfig('dcs/get_customer/x_brand_get');
        $x_version =  $this->getConfig('dcs/get_customer/x_version_get');
        $connectionTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_connection_timeout');
        $requestTimeout = $this->getConfig('dcs/dcs_api_timeout/dcs_request_timeout');

        try {
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("X-BRAND", $x_brand);
            $this->curl->addHeader("X-CLIENT", $x_client);
            $this->curl->addHeader("X-VERSION", $x_version);
            $this->curl->addHeader("Authorization", $idToken);
            $options =  [CURLOPT_CONNECTTIMEOUT => $connectionTimeout];
            $this->curl->setOptions($options);
            $this->curl->setTimeout($requestTimeout);
            $this->curl->get($url);
            //response will contain the output of curl request
            $response = $this->curl->getBody();
            $httpStatusCode = $this->curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $this->dcsLogger->error('get customer email API response: ' . json_encode($response));
                $this->dcsLogger->error('get customer email Url: ' . json_encode($url));
            } else {
                if ($this->dcsHelper->isLogEnabled()) {
                    $this->dcsLogger->info('Get customer email API response' . json_encode($response));
                }
                if ($response) {
                    $response = $this->json->unserialize($response);
                }
            }
        } catch (\Exception $e) {
            $this->dcsLogger->error('Get customer email API not working' . $e->getMessage());
        }
        return $response;
    }

    /**
     * Get store config value for DCS
     *
     * @param sting $path
     * @param int $storeId
     * @return string
     */
    protected function getConfig($path, $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
