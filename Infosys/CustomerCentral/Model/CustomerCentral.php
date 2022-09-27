<?php

/**
 * @package   Infosys/CustomerCentral
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerCentral\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Infosys\CustomerCentral\Api\CustomerCentralInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;
use Infosys\CustomerCentral\Helper\Data;
use Infosys\CustomerCentral\Logger\CustomerCentralLogger;
use Magento\Framework\Encryption\EncryptorInterface;
use Infosys\ProductsByVin\Helper\Data as BrandHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CustomerCentral implements CustomerCentralInterface
{
    /**
     * API Request data
     *
     * @var array
     */
    protected $requestData = [
        'customerCommunication' => [
            'dataArea' => [
                'customerCommunicationPayload' => [
                    'customerProfile' => [
                        'customer' => [
                            'customerType' => '',
                            'customerID' => '',
                            'salutation' => '',
                            'firstNM' => '',
                            'lastNM' => '',
                            'middleInitial' => '',
                            'gender' => '',
                            'nameSuffix' => '',
                            'communicationInfo' => [
                                'postalAddressInfo' => [],
                                'shippingAddressInfo' => [],
                                'phoneInfo' => [],
                                'emailInfo' => []
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];
    /**
     * API Address Request data
     *
     * @var array
     */
    protected $addressRequestData = [
        'addressLine1' => '',
        'addressLine2' => '',
        'city' => '',
        'stateProvinceCD' => '',
        'postalCD' => '',
        'countryNM' => ''
    ];
    /**
     * API Email Request data
     *
     * @var array
     */
    protected $emailRequestData = ['emailAddress' => ''];
    /**
     * API Phone number Request data
     *
     * @var array
     */
    protected $phoneNumberRequestData = ['areaCD' => '', 'phoneNO' => ''];
    /**
     * Default make value
     *
     * @var string
     */
    protected $make = 'TOYOTA';
    /**
     * @var string
     */
    protected $customerSaveToken = '';
    /**
     * @var string
     */
    protected $partsOnlinePurchaseToken = '';
    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var CustomerCentralLogger
     */
    protected $CCLogger;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var BrandHelper
     */
    protected $brandHelper;

    /**
     * Curl Status for 200
     */
    const CURL_STATUS = 200;

    /**
     * @var customerFactory
     */
    protected $customerFactory;

    /**
     * @var addressFactory;
     */
    protected $addressFactory;

    /**
     * @var timezone
     */
    protected $timezone;

    /**
     * Constructor function
     *
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param CustomerCentralLogger $CCLogger
     * @param Data $helper
     * @param EncryptorInterface $encryptor
     * @param BrandHelper $brandHelper
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        CustomerRepositoryInterface $customerRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        CustomerCentralLogger $CCLogger,
        Data $helper,
        EncryptorInterface $encryptor,
        BrandHelper $brandHelper,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        TimezoneInterface $timezone
    ) {
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->CCLogger = $CCLogger;
        $this->helper = $helper;
        $this->encryptor = $encryptor;
        $this->brandHelper = $brandHelper;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->timezone = $timezone;
    }
    /**
     * Get API token based on resource
     *
     * @param string $resource
     * @return string
     */
    public function getToken($resource)
    {

        //TODO cache token until it expires to minimize API calls
        $tokenUrl = $this->getConfig('customer_central/token_api/token_url');
        $grantType = $this->getConfig('customer_central/token_api/grant_type');
        $clientId = $this->getConfig('customer_central/token_api/client_id');
        $clientSecret = $this->getConfig('customer_central/token_api/client_secret');
        $deEncryptClientSecret = $this->encryptor->decrypt($clientSecret);
        $connectionTimeout = $this->getConfig('customer_central/cc_api_timeout/cc_connection_timeout');
        $requestTimeout = $this->getConfig('customer_central/cc_api_timeout/cc_request_timeout');
        $options =  [CURLOPT_CONNECTTIMEOUT => $connectionTimeout];
        $params = [
            'grant_type' => $grantType,
            'client_id' => $clientId,
            'client_secret' => $deEncryptClientSecret,
            'resource' => $resource
        ];
        $this->curl->addHeader("Content-Type", "application/x-www-form-urlencoded");
        try {
            $this->curl->setOptions($options);
            $this->curl->setTimeout($requestTimeout);
            $this->curl->post($tokenUrl, $params);

            //response will contain the output of curl request
            $response = $this->curl->getBody();
            if ($this->helper->isLogEnabled()) {
                $this->CCLogger->info('Customer Central Token API response: ' . $response);
            }
            if ($response) {
                $response = $this->json->unserialize($response);
                if (isset($response['access_token'])) {
                    return $response['access_token'];
                }
            }
        } catch (\Exception $e) {
            $this->CCLogger->error('Customer Central Token API not working: ' . $e->getMessage());
        }
    }
    /**
     * API to save customer info in customer central
     *
     * @param string $customerInfo
     * @return array
     */
    public function saveCustomerDetails($customerInfo)
    {
        $responseData = ['customerCentralId' => '', 'shippingId' => [], 'error' => ''];
        $resource = $this->getConfig('customer_central/save_customer_api/resource');
        if (!$this->customerSaveToken) {
            $this->customerSaveToken = $this->getToken($resource);
        }
        $token = $this->customerSaveToken;
        //TODO handle if there is no token returned with error.
        $url = $this->getConfig('customer_central/save_customer_api/save_customer_api_url');
        $clientId = $this->getConfig('customer_central/save_customer_api/x_ibm_client_id');
        $action =  $this->getConfig('customer_central/save_customer_api/action');
        $SourceSystem =  $this->getConfig('customer_central/save_customer_api/source_system');
        $SourceRecordType =  $this->getConfig('customer_central/save_customer_api/source_record_tpe');
        $ServiceRecordType =  $this->getConfig('customer_central/save_customer_api/service_record_type');
        $connectionTimeout = $this->getConfig('customer_central/cc_api_timeout/cc_connection_timeout');
        $requestTimeout = $this->getConfig('customer_central/cc_api_timeout/cc_request_timeout');
        $date = new \DateTime();
        $dt = $this->timezone->date($date)->format(\DateTimeInterface::ATOM);
        $options =  [
            CURLOPT_CONNECTTIMEOUT => $connectionTimeout
        ];

        $logRequest = "";

        try {
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer " . $token);
            $this->curl->addHeader("x-ibm-client-id", $clientId);
            $this->curl->addHeader("action", $action);
            $this->curl->addHeader("SourceSystem", $SourceSystem);
            $this->curl->addHeader("SourceRecordType", $SourceRecordType);
            $this->curl->addHeader("ServiceRecordType", $ServiceRecordType);
            $this->curl->addHeader("StartDT", $dt);
            $this->curl->addHeader("SourceSubmissionDT", $dt);
            $this->curl->setOptions($options);
            $this->curl->setTimeout($requestTimeout);

            //logging savecustomerdetails request
            $logRequest = "curl --location --request POST '" . $url . "'";
            $logRequest .= " --header 'x-ibm-client-id: " . $clientId . "'";
            $logRequest .= " --header 'action: " . $action . "'";
            $logRequest .= " --header 'SourceSystem: " . $SourceSystem . "'";
            $logRequest .= " --header 'SourceRecordType: " . $SourceRecordType . "'";
            $logRequest .= " --header 'ServiceRecordType: " . $ServiceRecordType . "'";
            $logRequest .= " --header 'Authorization: Bearer " . $token . "'";
            $logRequest .= " --header 'StartDT: " . $dt . "'";
            $logRequest .= " --header 'SourceSubmissionDT: " . $dt . "'";
            $logRequest .= " --header 'Content-Type: application/json'";
            $logRequest .= " --data-raw '" . $customerInfo . "'";

            $this->curl->post($url, $customerInfo);
            //response will contain the output of curl request
            $response = $this->curl->getBody();

            //non 200 http response
            $httpStatusCode = $this->curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $error = 'SaveCustomerDetails API request is failing and timeout. HTTP status code: ' . $httpStatusCode;
                $this->CCLogger->error('SaveCustomerDetails API request is failing.HTTP status code: ' . $httpStatusCode);
                $this->CCLogger->error('SaveCustomerDetails API request: ' . $logRequest);
                $this->CCLogger->error('SaveCustomerDetails API response' . $response);
            }

            //Checking status type
            $responsecopy = json_decode($response, true);
            $statusType = "";
            $warning = [];
            
            if (isset($responsecopy['customerCommunication']['dataArea']['serviceFooter']['statusType'])) {
                $statusType = $responsecopy['customerCommunication']['dataArea']['serviceFooter']['statusType'];
            }
            if(isset($responsecopy['customerCommunication']['dataArea']['serviceFooter']['warning'])){
                $warning = $responsecopy['customerCommunication']['dataArea']['serviceFooter']['warning'];
            }
            if ($statusType != 'SUCCESSFUL_TRANSACTION') {
                if(isset($responsecopy['customerCommunication']['dataArea']['serviceFooter']['statusDescription'])){
                    $error = $responsecopy['customerCommunication']['dataArea']['serviceFooter']['statusDescription'];
                }
                $this->CCLogger->error('SaveCustomerDetails API request: ' . $logRequest);
                $this->CCLogger->error('SaveCustomerDetails API response' . $response);
            } else {
                if ($this->helper->isLogEnabled()) {
                    $this->CCLogger->info('SaveCustomerDetails API request: ' . $logRequest);
                    $this->CCLogger->info('SaveCustomerDetails API response' . $response);
                }
            }

            if ($response) {
                $response = $this->json->unserialize($response);
                if (isset($response['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer']['customerID'])) {
                    $responseData['customerCentralId'] = $response['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer']['customerID'];
                }
                if (isset($response['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer']['communicationInfo']['shippingAddressInfo'])) {
                    foreach ($response['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer']['communicationInfo']['shippingAddressInfo'] as $shippingData) {
                        if (isset($shippingData['shippingAddressId']) && isset($shippingData['postalCD'])) {
                            $data = [
                                'shippingAddressId' => $shippingData['shippingAddressId'],
                                'postalCD' => $shippingData['postalCD']
                            ];
                            $responseData['shippingId'][] = $data;
                        }
                    }
                }
                if (isset($error)){
                    $responseData['error'] = $error;
                }
                if (isset($warning)){
                    $responseData['warning'] = $warning;
                }

            }
        } catch (\Exception $e) {
            $this->CCLogger->error('saveCustomerDetails API not working' . $e->getMessage());
            $this->CCLogger->error('SaveCustomerDetails API request: ' . $logRequest);
        }
        return $responseData;
    }
    /**
     * Save order data to customer central
     *
     * @param array $requestData
     * @return void
     */
    public function partsOnlinePurchase($requestData)
    {
        $resource = $this->getConfig('customer_central/parts_online_purchase/resource');
        if (!$this->partsOnlinePurchaseToken) {
            $this->partsOnlinePurchaseToken = $this->getToken($resource);
        }
        $token = $this->partsOnlinePurchaseToken;
        $url = $this->getConfig('customer_central/parts_online_purchase/parts_online_purchase_api_url');
        $clientId = $this->getConfig('customer_central/parts_online_purchase/x_ibm_client_id');
        $action =  $this->getConfig('customer_central/parts_online_purchase/action');
        $SourceSystem =  $this->getConfig('customer_central/parts_online_purchase/source_system');
        $SourceRecordType =  $this->getConfig('customer_central/parts_online_purchase/source_record_tpe');
        $ServiceRecordType =  $this->getConfig('customer_central/parts_online_purchase/service_record_type');
        $connectionTimeout = $this->getConfig('customer_central/cc_api_timeout/cc_connection_timeout');
        $requestTimeout = $this->getConfig('customer_central/cc_api_timeout/cc_request_timeout');
        $options =  [
            CURLOPT_CONNECTTIMEOUT => $connectionTimeout
        ];

        try {
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer " . $token);
            $this->curl->addHeader("x-ibm-client-id", $clientId);
            $this->curl->addHeader("action", $action);
            $this->curl->addHeader("SourceSystem", $SourceSystem);
            $this->curl->addHeader("SourceRecordType", $SourceRecordType);
            $this->curl->addHeader("ServiceRecordType", $ServiceRecordType);            
            $this->curl->setOptions($options);
            $this->curl->setTimeout($requestTimeout);

            $params = '{
		  "CustomerOrderMapping": {
			"dataArea": {
			  "CustomerOrderMappingPayload": {
				"CustomerOrder": {
				  "customer": {
					"customerId": "' . $requestData['customerId'] . '"
				  },
				  "OrderInfo": {
					"orderId": "' . $requestData['orderId'] . '",
					"shippingAddressId": "' . $requestData['shippingAddressId'] . '",
					"make": "' . $requestData['make'] . '"
				  }
				}
			  }
			}
		  }
		}';
            $this->curl->post($url, $params);
            $response = $this->curl->getBody();

            //logging partsonlinepurchase request
            $logRequest =  "curl --location --request POST '" . $url . "'";
            $logRequest .= " --header 'x-ibm-client-id: " . $clientId . "'";
            $logRequest .= " --header 'sourcesystem: " . $SourceSystem . "'";
            $logRequest .= " --header 'action: " . $action . "'";
            $logRequest .= " --header 'sourcerecordtype: " . $SourceRecordType . "'";
            $logRequest .= " --header 'servicerecordtype: " . $ServiceRecordType . "'";
            $logRequest .= " --header 'Authorization: Bearer " . $token . "'";
            $logRequest .= " --header 'Content-Type: application/json'";
            $logRequest .= " --data-raw '" . $params . "'";

            //non 200 http response
            $httpStatusCode = $this->curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $error = 'partsOnlinePurchase API failing and timeout. HTTP status code: ' . $httpStatusCode;
                $this->CCLogger->error('PartsOnlinePurchase API request is failing. HTTP status code: ' . $httpStatusCode);
                $this->CCLogger->error('PartsOnlinePurchase API request: ' . $logRequest);
                $this->CCLogger->error('PartsOnlinePurchase API response: ' . $response);
            }

            //Checking status type
            $responsecopy = json_decode($response, true);
            $statusType = "";
            if (isset($responsecopy['CustomerOrderMapping']['dataArea']['serviceFooter']['statusType'])) {
                $statusType = $responsecopy['CustomerOrderMapping']['dataArea']['serviceFooter']['statusType'];
            }
            if ($statusType != 'SUCCESSFUL_TRANSACTION') {
                if(isset($responsecopy['customerCommunication']['dataArea']['serviceFooter']['statusDescription'])){
                    $error = $responsecopy['customerCommunication']['dataArea']['serviceFooter']['statusDescription'];
                }
                $this->CCLogger->error('PartsOnlinePurchase API request: ' . $logRequest);
                $this->CCLogger->error('PartsOnlinePurchase API response: ' . $response);
            } else {
                if ($this->helper->isLogEnabled()) {
                    $this->CCLogger->info('PartsOnlinePurchase API request: ' . $logRequest);
                    $this->CCLogger->info('PartsOnlinePurchase API response: ' . $response);
                }
            }

            if ($response) {
                $response = $this->json->unserialize($response);
                if(isset($error)){
                    $response['CustomerOrderMapping']['dataArea']['serviceFooter']['error'] = $error;
                }
            }
            return $response;
        } catch (\Exception $e) {
            $this->CCLogger->error('partsOnlinePurchase API not working' . $e->getMessage());
        }
    }
    /**
     * Add customer central Id in custom attribute
     *
     * @param string $customerCentralId
     * @param string $customerEmail
     * @param int $customerId
     * @return void
     */
    public function updateCustomerCentralId($customerCentralId, $customerEmail, $customerId) : void
    {
        try {
            $customer =  $this->customerRepository->getById($customerId);
            $customer->setCustomAttribute('customer_central_id', $customerCentralId);
            $this->customerRepository->save($customer);
        } catch (\Exception $e) {
            $this->CCLogger->error('updateCustomerCentralId ' . $e->getMessage());
        }
    }
    /**
     * Get store config value for customer central API
     *
     * @param sting $path
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
    /**
     * Sync guest customer in checekout place
     *
     * @param object $customerData
     * @return array
     */
    public function syncGuestCustomerInCheckout($customerData)
    {
        $requestData = $this->requestData;
        $emailRequestData = $this->emailRequestData;
        $phoneNumberRequestData = $this->phoneNumberRequestData;
        $customerInfo = $requestData['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer'];
        $customerInfo['firstNM'] = $customerData->getFirstName();
        $customerInfo['lastNM'] = $customerData->getLastName();
        $emailRequestData['emailAddress'] = $customerData->getEmail();
        $phoneNumberRequestData['phoneNO'] = $customerData->getTelephoneNumber() ?
            substr($customerData->getTelephoneNumber(), -7) : '';
        $phoneNumberRequestData['areaCD'] = $customerData->getTelephoneNumber() ?
            substr($customerData->getTelephoneNumber(), 0, 3) : '';
        $customerInfo['communicationInfo']['emailInfo'][] = $emailRequestData;
        $customerInfo['communicationInfo']['phoneInfo'][] = $phoneNumberRequestData;
        $customerInfo['communicationInfo']['postalAddressInfo'][] = $this->addressRequestData;
        $customerInfo['communicationInfo']['shippingAddressInfo'][] = $this->addressRequestData;
        $requestData['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer'] = $customerInfo;
        $requestData = $this->json->serialize($requestData);
        return $this->saveCustomerDetails($requestData);
    }
    /**
     * Sync data on create and update customer for logged in customer
     *
     * @param object $customerData
     * @return array
     */
    public function syncCustomerOnUpdate($customerData)
    {
        //Customer Billing Address
        $billingAddressId = $customerData->getDefaultBilling();
        if ($billingAddressId) {
            $billingAddress = $this->addressFactory->create()->load($billingAddressId);
            $billingAddressData = [
                'addressLine1' => isset($billingAddress->getStreet()[0]) ?
                    $billingAddress->getStreet()[0] : '',
                'addressLine2' => isset($billingAddress->getStreet()[1]) ?
                    $billingAddress->getStreet()[1] : '',
                'city' => $billingAddress['city'],
                'stateProvinceCD' => $billingAddress['region'],
                'postalCD' => $billingAddress['postcode'],
                'countryNM' => $billingAddress['country_id']
            ];
        }
        //Customer Shiiping Address
        $shippingAddressId = $customerData->getDefaultShipping();
        if ($shippingAddressId) {
            $shippingAddress = $this->addressFactory->create()->load($shippingAddressId);
            $shippingAddressData = [
                'addressLine1' => isset($shippingAddress->getStreet()[0]) ?
                    $shippingAddress->getStreet()[0] : '',
                'addressLine2' => isset($shippingAddress->getStreet()[1]) ?
                    $shippingAddress->getStreet()[1] : '',
                'city' => $shippingAddress['city'],
                'stateProvinceCD' => $shippingAddress['region'],
                'postalCD' => $shippingAddress['postcode'],
                'countryNM' => $shippingAddress['country_id']
            ];
        }
        $requestData = $this->requestData;
        $customerInfo = $requestData['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer'];
        if( ( $customerData->getCustomAttribute('customer_central_id') !== null ) && $customerData->getCustomAttribute('customer_central_id')->getValue()){
            $customerInfo['customerID'] = $customerData->getCustomAttribute('customer_central_id')->getValue();
        }      
        $customerInfo['firstNM'] = $customerData->getFirstName();
        $customerInfo['lastNM'] = $customerData->getLastName();
        $customerInfo['middleInitial'] = $customerData->getMiddlename();
        $customerInfo['gender'] = $customerData->getGender();
        $customerInfo['nameSuffix'] = $customerData->getSuffix();
        $customerInfo['communicationInfo']['emailInfo'][] = ['emailAddress' => $customerData->getEmail()];

        if ($billingAddressId && $shippingAddressId) {
            $customerInfo['communicationInfo']['postalAddressInfo'][] = $billingAddressData;
            $customerInfo['communicationInfo']['shippingAddressInfo'][] = $shippingAddressData;
        } else {
            $customerInfo['communicationInfo']['postalAddressInfo'][] = $this->addressRequestData;
            $customerInfo['communicationInfo']['shippingAddressInfo'][] = $this->addressRequestData;
        }
        $phoneNumberRequestData = $this->phoneNumberRequestData;
        if ($customerData->getCustomAttribute('phone_number') && $customerData->getCustomAttribute('phone_number')->getValue()) {
            $customerPhoneNumber = $customerData->getCustomAttribute('phone_number')->getValue();
            $phoneNumberRequestData['phoneNO'] = $customerPhoneNumber ?
                substr($customerPhoneNumber, -7) : '';
            $phoneNumberRequestData['areaCD'] = $customerPhoneNumber ?
                substr($customerPhoneNumber, 0, 3) : '';
        }
        $customerInfo['communicationInfo']['phoneInfo'][] = $phoneNumberRequestData;
        $requestData['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer'] = $customerInfo;

        $requestData = $this->json->serialize($requestData);
        $customerCentral = $this->saveCustomerDetails($requestData);
        if (!empty($customerCentral['customerCentralId'])) {
            $this->updateCustomerCentralId($customerCentral['customerCentralId'], $customerData->getEmail(), $customerData->getId());
        }
        return $customerCentral;
    }
    /**
     * Sync data on manually from admin
     *
     * @param object $customer
     * @return array
     */
    public function manualCustomerSync($customer) : array
    { 
        $returnData = [];
        $response = $this->syncCustomerOnUpdate($customer);

        if($response && isset($response['error']) && $response['error'] != ''){
            $returnData['error'][] = $response['error'];
            return $returnData;
        }

        if($response && isset($response['warning']) && count($response['warning'])){
            $returnData['warning'] = $response['warning'];
            return $returnData;
        }

        $orders = $this->getCustomerOrder($customer->getId());
        if ($response && $orders) {
            foreach ($orders as $order) {
                if(is_null($order->getCustomerCentralId())){
                    $orderPostalCode = $order->getShippingAddress()->getPostcode();
                    foreach ($response['shippingId'] as $shippingData) {
                        if ($shippingData['postalCD'] == $orderPostalCode) {
                            $make = '';
                            $storeId = $order->getStore()->getId();
                            $store_brand = $this->brandHelper->getEnabledBrands($storeId);
                            if ($store_brand) {
                                $brands = explode(',', $store_brand);
                                $make = $brands[0];
                            }
                            if ($this->helper->isLogEnabled()) {
                                $this->CCLogger->info('Manual Customer Sync make value: ' . $make);
                            }
                            if (!$make) {
                                $make = $this->make;
                            }
                            if ($this->helper->sendOrderIncrementId()) {
                                $orderId = $order->getIncrementId();
                            } else {
                                $orderId = $order->getId();
                            }
                            $requestData = [
                                'customerId' => $response['customerCentralId'],
                                'orderId' => $orderId,
                                'shippingAddressId' => $shippingData['shippingAddressId'],
                                'make' => $make
                            ];
                            $responseData = $this->partsOnlinePurchase($requestData);
                            //Save customer central Id into order
                            $order->getShippingAddress()->setCustomerCentralAddressId($response['shippingId'][0]['shippingAddressId']);
							if (!empty($response['customerCentralId'])) {
								$order->setCustomerCentralId($response['customerCentralId']);
							}
                            $order->save();
                            if(isset($responseData['CustomerOrderMapping']['dataArea']['serviceFooter']['error'])){
                                $returnData['error'][] = $responseData['CustomerOrderMapping']['dataArea']['serviceFooter']['error'];
                            }
                            elseif (isset($responseData['CustomerOrderMapping']['dataArea']['serviceFooter']['businessException'][0]['errorDescription'])) {
                                $returnData['warning'][] = $order->getIncrementId()."-".$responseData['CustomerOrderMapping']['dataArea']['serviceFooter']['businessException'][0]['errorDescription'];
                            }
                            if ($this->helper->isLogEnabled()) {
                                $logResponse = $this->json->serialize($responseData);
                                $this->CCLogger->info('Manual Customer Sync' . $logResponse);
                            }
                        }
                    }
                }
            }
        }
        return $returnData;

        
    }
    /**
     * Get customer order
     *
     * @param int $customerId
     * @return void
     */
    protected function getCustomerOrder($customerId)
    {
        $searchBuilder = $this->searchCriteriaBuilder->addFilter(
            'customer_id',
            $customerId,
            'eq'
        )->create();
        $orders = $this->orderRepository->getList($searchBuilder);
        return $orders;
    }
    /**
     * Sync data on order place after for logged in customer
     *
     * @param object $order
     * @return array
     */
    public function syncCustomerOnOrderPlace($order)
    {
        $customerOrderSyncResponse = [];
        $orderSyncError = '';
        $orderSyncWarning = '';
        $requestData = $this->requestData;
        $emailRequestData = $this->emailRequestData;
        $phoneNumberRequestData = $this->phoneNumberRequestData;
        $customerInfo = $requestData['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer'];
        $shippingdata = $order->getShippingAddress()->getData();
        $shippingaddressData = [
            'addressLine1' => isset($order->getShippingAddress()->getStreet()[0]) ?
                $order->getShippingAddress()->getStreet()[0] : '',
            'addressLine2' => isset($order->getShippingAddress()->getStreet()[1]) ?
                $order->getShippingAddress()->getStreet()[1] : '',
            'city' => $shippingdata['city'],
            'stateProvinceCD' => $shippingdata['region'],
            'postalCD' => $shippingdata['postcode'],
            'countryNM' => $shippingdata['country_id']
        ];

        $billingdata = $order->getBillingAddress()->getData();
        $billingaddressData = [
            'addressLine1' => isset($order->getBillingAddress()->getStreet()[0]) ?
                $order->getBillingAddress()->getStreet()[0] : '',
            'addressLine2' => isset($order->getBillingAddress()->getStreet()[1]) ?
                $order->getBillingAddress()->getStreet()[1] : '',
            'city' => $billingdata['city'],
            'stateProvinceCD' => $billingdata['region'],
            'postalCD' => $billingdata['postcode'],
            'countryNM' => $billingdata['country_id']
        ];
        $emailRequestData['emailAddress'] = $shippingdata['email'];
        $phoneNumberRequestData['phoneNO'] = $shippingdata['telephone'] ?
            substr($shippingdata['telephone'], -7) : '';
        $phoneNumberRequestData['areaCD'] = $shippingdata['telephone'] ?
            substr($shippingdata['telephone'], 0, 3) : '';

        if($order->getCustomerId() !== null ){
            $customer =  $this->customerRepository->getById($order->getCustomerId());
            if( ( $customer->getCustomAttribute('customer_central_id') !== null ) && $customer->getCustomAttribute('customer_central_id')->getValue() ){
                $customerInfo['customerID'] = $customer->getCustomAttribute('customer_central_id')->getValue();
            }
        }
        
        $customerInfo['firstNM'] = $shippingdata['firstname'];
        $customerInfo['lastNM'] = $shippingdata['lastname'];
        $customerInfo['communicationInfo']['emailInfo'][] = $emailRequestData;
        $customerInfo['communicationInfo']['phoneInfo'][] = $phoneNumberRequestData;
        $customerInfo['communicationInfo']['postalAddressInfo'][] = $billingaddressData;
        $customerInfo['communicationInfo']['shippingAddressInfo'][] = $shippingaddressData;
        $requestData['customerCommunication']['dataArea']['customerCommunicationPayload']['customerProfile']['customer'] = $customerInfo;
        $requestData = $this->json->serialize($requestData);
        $response =  $this->saveCustomerDetails($requestData);

        if($response && isset($response['error']) && $response['error'] != ''){            
            $customerOrderSyncResponse = [
                'syncError' => $response['error']
            ];
            return $customerOrderSyncResponse;
        }

        $make = '';
        $storeId = $order->getStore()->getId();
        $store_brand = $this->brandHelper->getEnabledBrands($storeId);
        if ($store_brand) {
            $brands = explode(',', $store_brand);
            $make = $brands[0];
        }
        if ($this->helper->isLogEnabled()) {
            $this->CCLogger->info('Sync Customer on Order place make value: ' . $make);
        }
        if (!$make) {
            $make = $this->make;
        }
        if ($this->helper->sendOrderIncrementId()) {
            $orderId = $order->getIncrementId();
        } else {
            $orderId = $order->getId();
        }
        
        if (isset($response['shippingId'][0]['shippingAddressId'])) {
            $requestData = [
                'customerId' => $response['customerCentralId'],
                'orderId' => $orderId,
                'shippingAddressId' => $response['shippingId'][0]['shippingAddressId'],
                'make' => $make
            ];
            $responseData = $this->partsOnlinePurchase($requestData);
           
            $order->getShippingAddress()->setCustomerCentralAddressId($response['shippingId'][0]['shippingAddressId']);
			if (!empty($response['customerCentralId'])) {
				$order->setCustomerCentralId($response['customerCentralId']);
			}
            $order->save();

            if(isset($responseData['CustomerOrderMapping']['dataArea']['serviceFooter']['error'])){
                $orderSyncError = $responseData['CustomerOrderMapping']['dataArea']['serviceFooter']['error'];
            }

            if ($this->helper->isLogEnabled()) {
                $this->CCLogger->info('Shipping AddressId-' . $response['shippingId'][0]['shippingAddressId']);
            }

            if ($responseData) {
                $customerOrderSyncResponse = [
                    'customerId' => $response['customerCentralId'],
                    'Status_type' => $responseData['CustomerOrderMapping']['dataArea']['serviceFooter']['statusType'],
                    'syncError' => $orderSyncError
                ];
            }
        }

        return $customerOrderSyncResponse;
    }
}
