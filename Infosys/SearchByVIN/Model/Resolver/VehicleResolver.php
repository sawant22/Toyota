<?php

/**
 * @package Infosys/SearchByVIN
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SearchByVIN\Model\Resolver;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Infosys\YMMSearchGraphQL\Model\EFCapi;
use Infosys\SearchByVIN\Logger\VISLogger;
use Infosys\Vehicle\Model\ResourceModel\Vehicle\Collection as VehicleCollection;
use Magento\Store\Model\StoreManager;

/**
 * Vin search GraphQL request processing
 */
class VehicleResolver implements ResolverInterface
{
    const CURL_STATUS = 200;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Infosys\SearchByVIN\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Infosys\YMMSearchGraphQL\Model\EFCapi
     */
    protected $EFCapi;

    /**
     * @var \Infosys\SearchByVIN\Logger\VISLogger
     */
    protected $VISLogger;

    /**
     * @var VehicleCollection
     */
    protected $vehicleCollection;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * Constructor function
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Infosys\SearchByVIN\Helper\Data $helperData
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Infosys\YMMSearchGraphQL\Model\EFCapi $EFCapi
     * @param VISLogger $VISLogger
     * @param VehicleCollection $vehicleCollection
     * @param StoreManager $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Infosys\SearchByVIN\Helper\Data $helperData,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Infosys\YMMSearchGraphQL\Model\EFCapi $EFCapi,
        VISLogger $VISLogger,
        VehicleCollection $vehicleCollection,
        StoreManager $storeManager
    ) {
        $this->_curl = $curl;
        $this->_json = $json;
        $this->helperData = $helperData;
        $this->product = $product;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->EFCapi = $EFCapi;
        $this->VISLogger = $VISLogger;
        $this->vehicleCollection = $vehicleCollection;
        $this->storeManager = $storeManager;
    }

    /**
     * Resolver to get vehicle attributes from VIN api
     *
     * @param Field $field
     * @param [type] $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $vin = $args['vin'];
        $attributes = $this->getAttributes($vin);
        return $attributes;
    }

    /**
     * Attributes data
     *
     * @param array $vin
     * @return array
     */
    public function getAttributes($vin): array
    {
        $result = [];
        $res = [];
        $res1 = [];
        $clientid = $this->helperData->getConfig('searchbyvin/general/clientid');
        $clientsecret = $this->helperData->getConfig('searchbyvin/general/clientsecret');
        $granttype = $this->helperData->getConfig('searchbyvin/general/granttype');
        $resource = $this->helperData->getConfig('searchbyvin/general1/apiazureresource');
        $url = $this->helperData->getConfig('searchbyvin/general/tokenurl');
        $connectionTimeout = $this->helperData->getConfig('searchbyvin/vis_api_timeout/vis_connection_timeout');
        $requestTimeout = $this->helperData->getConfig('searchbyvin/vis_api_timeout/vis_request_timeout');
        $options =  [
            CURLOPT_CONNECTTIMEOUT => $connectionTimeout
        ];
        try {
            $params = [
                "client_id" => $clientid,
                "client_secret" => $clientsecret,
                "grant_type" => $granttype,
                "resource" => $resource
            ];
            $this->_curl->setOptions($options);
            $this->_curl->setTimeout($requestTimeout);
            $this->_curl->post($url, $params);
            $response = $this->_curl->getBody();

            //non 200 http response
            $httpStatusCode = $this->_curl->getStatus();
            if ($httpStatusCode != self::CURL_STATUS) {
                $this->VISLogger->error('VIS API request is failing.HTTP status code: ' . $httpStatusCode);
                $this->VISLogger->error('VIS API response' . $response);
            }

            if ($response) {
                $this->VISLogger->info('Token API response' . $response);
                $res = $this->getJsonDecode($response);
            }
        } catch (\Exception $e) {
            $this->VISLogger->error('Token API not working' . $e->getMessage());
        }
        if (isset($res['access_token'])) {
            $token = $res['access_token'];
            $ibmclientid = $this->helperData->getConfig('searchbyvin/general1/ibmclientid');
            $bodyid = $this->helperData->getConfig('searchbyvin/general1/bodyid');
            $url1 = $this->helperData->getConfig('searchbyvin/general1/visapiurl') . $vin;
            try {
                $headers = [
                    'x-ibm-client-id: ' . $ibmclientid,
                    'bodid: ' . $bodyid,
                    'CreatorNameCode: epc',
                    'Authorization: Bearer ' . $token
                ];
                $optionArray = [
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_CONNECTTIMEOUT => $connectionTimeout
                ];
                $this->_curl->setOptions($optionArray);
                $this->_curl->setTimeout($requestTimeout);
                $this->_curl->get($url1);
                $response1 = $this->_curl->getBody();

                //non 200 http response
                $httpStatusCode = $this->_curl->getStatus();
                if ($httpStatusCode != self::CURL_STATUS) {
                    $this->VISLogger->error('VIS API request is failing.HTTP status code: ' . $httpStatusCode);
                    $this->VISLogger->error('VIS API response' . $response1);
                }

                if ($response1) {
                    $this->VISLogger->info('VIS API response: ' . $response1);
                    $res1 = $this->getJsonDecode($response1);
                }
            } catch (\Exception $e) {
                $this->VISLogger->error('VIS API not working: ' . $e->getMessage());
            }
            //check api response
            try {
                if ($res1['Show']) {
                    $keys = array_keys($res1['Show']['VehicleInventoryBOD']['DataArea']);
                    if (in_array('VehicleInventory', $keys)) {
                        $model_year = $res1['Show']['VehicleInventoryBOD']['DataArea']['VehicleInventory']['ModelYear'];
                        $model_code = $res1['Show']['VehicleInventoryBOD']['DataArea']['VehicleInventory']['Model'];
                        $res = $this->EFCapi->getVehicleImage($model_year, $model_code);
                        $vehicle_image = '';
                        $vehicle_placeholder_image = $this->helperData->getConfig('epc_config/vehicle_placeholder/placeholder');
                        if ($vehicle_placeholder_image) {
                            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                            $vehicle_placeholder_image = $mediaUrl . 'catalog/vehicle/placeholder/' . $vehicle_placeholder_image;
                            $vehicle_image = $vehicle_placeholder_image;
                        }
                        if (isset($res['years_list'])) {
                            if (isset($res['years_list'][0]['series_list'][0]['trims'][0]['images']) &&
                                isset($res['years_list'][0]['series_list'][0]['trims'][0]['images']['grade_car_jelly_image'])
                            ) {
                                $image = $res['years_list'][0]['series_list'][0]['trims'][0]['images']['grade_car_jelly_image'];
                                if ($image) {
                                    $vehicle_image = $res['imageurl'] . $image;
                                }
                            }
                        }
                        $vehicleAttributes = $this->getVehicleAttributes($model_year, $model_code);
                        $arr = [
                            'entity_id' => $vehicleAttributes['entity_id'],
                            'model_year' => $model_year,
                            'model_code' => $model_code,
                            'make' => $vehicleAttributes['brand'],
                            'model_name' => $vehicleAttributes['series_name'],
                            'grade' => $vehicleAttributes['grade'],
                            'driveline' => $vehicleAttributes['driveline'],
                            'body_style' => $vehicleAttributes['body_style'],
                            'vehicle_image' => $vehicle_image
                        ];
                        $result['allRecords'][] = $arr;
                        $result['message'] = 'Success';
                    } else {
                        $result['allRecords'][] = null;
                        $result['message'] = "No data found";
                    }
                } else {
                    $result['allRecords'][] = null;
                    $result['message'] = "API data not available";
                }
            } catch (\Exception $e) {
                $this->VISLogger->error('VIS API not working' . $e->getMessage());
            }
        }
        return $result;
    }

    /**
     * Json data
     *
     * @param array $response
     * @return array
     */
    public function getJsonDecode($response)
    {
        return $this->_json->unserialize($response); // it's same as like json_decode
    }

    /**
     * Method to get vehicle series name and body style
     *
     * @param int $model_year
     * @param string $model_code
     * @return array
     */
    public function getVehicleAttributes($model_year, $model_code)
    {
        $attributesArray = [
            'entity_id' => '',
            'brand' => '',
            'series_name' => '',
            'grade' => '',
            'driveline' => '',
            'body_style' => ''
        ];
        $collection = $this->vehicleCollection->addFieldToSelect('*')
            ->addFieldToFilter('model_year', ['eq' => $model_year])
            ->addFieldToFilter('model_code', ['eq' => $model_code]);
        if (!empty($collection)) {
            $vehicle = $collection->getFirstItem();
            $attributesArray = [
                'entity_id' => $vehicle->getEntityId(),
                'brand' => $vehicle->getBrand(),
                'series_name' => $vehicle->getSeriesName(),
                'grade' => $vehicle->getGrade(),
                'driveline' => $vehicle->getDriveline(),
                'body_style' => $vehicle->getBodyStyle()
            ];
        }
        return $attributesArray;
    }
}
