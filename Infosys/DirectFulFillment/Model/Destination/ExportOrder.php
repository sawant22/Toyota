<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);
namespace Infosys\DirectFulFillment\Model\Destination;

use Magento\Framework\HTTP\Client\Curl;
use Infosys\DirectFulFillment\Helper\Data;
use Infosys\DirectFulFillment\Logger\DDOALogger;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Filesystem;
use Magento\Framework\Encryption\EncryptorInterface;
use Xtento\OrderExport\Model\DestinationFactory;
use Magento\Backend\Model\Session;

class ExportOrder extends \Xtento\OrderExport\Model\Destination\AbstractClass
{
    const DDOA_DEFAULT_TIMEOUT =  'df_config/ddoa_api_timeout/ddoa_connection_timeout';
    const DDOA_REQUEST_TIMEOUT =  'df_config/ddoa_api_timeout/ddoa_request_timeout';

    protected Data $helper;
   
    protected DDOALogger $ddoaLogger;

    /**
     * Constructor function
     *
     * @param Data $helper
     * @param Curl $curl
     * @param DDOALogger $ddoaLogger
     * @param Context $context
     * @param Registry $registry
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param Filesystem $filesystem
     * @param EncryptorInterface $encryptor
     * @param DestinationFactory $destinationFactory
     * @param Session $backendSession
     */
    public function __construct(
        Data $helper,
        Curl $curl,
        DDOALogger $ddoaLogger,
        Context $context,
        Registry $registry,
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        Filesystem $filesystem,
        EncryptorInterface $encryptor,
        DestinationFactory $destinationFactory,
        Session $backendSession
    ) {
        parent::__construct(
            $context,
            $registry,
            $objectManager,
            $scopeConfig,
            $dateTime,
            $filesystem,
            $encryptor,
            $destinationFactory
        );
        $this->helper = $helper;
        $this->curl = $curl;
        $this->ddoaLogger = $ddoaLogger;
        $this->backendSession = $backendSession;
    }
    /**
     * Sending Order data to DDOA API
     *
     * @param array $fileArray
     */
    public function saveFiles($fileArray): void
    {
        $accessToken =  $this->helper->getAccessToken();
        $connectionTimeout = $this->scopeConfig->getValue(self::DDOA_DEFAULT_TIMEOUT);
        $requestTimeout = $this->scopeConfig->getValue(self::DDOA_REQUEST_TIMEOUT);
        foreach ($fileArray as $data => $value) {
            if ($this->helper->isLogEnabled()) {
                $this->ddoaLogger->info('Info: ' . $value);
            }
            $soapXml =  $this->helper->getSoapXmlRequest($value);

            $api_url = $this->helper->getDDOAUrl();

            $curl = curl_init();

            try {
                curl_setopt_array($curl, [
                    CURLOPT_URL => $api_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => $requestTimeout,
                    CURLOPT_CONNECTTIMEOUT => $connectionTimeout,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $soapXml,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $accessToken,
                        'Content-Type: application/xml'
                    ],
                ]);
                $response = curl_exec($curl);
                //converting xml response into array to check errors
                $responseArray = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
                $xml = new \SimpleXMLElement($responseArray);
                $responseArray = json_decode(json_encode((array)$xml), true);
                $curlErrNo = curl_errno($curl);
                $httpResponseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
                if ($curlErrNo || $httpResponseCode != 200) {
                    $this->addError($api_url, $accessToken, $soapXml, $response);
                } else {                    
                    if (isset($responseArray['soapenvBody']['trProcessMessageResponse']['trpayload']['trcontent']['_5ConfirmBOD']['_5ConfirmBODDataArea']['_5BOD']['_9BODSuccessMessage'])) {
                        $this->addSuccess($api_url, $accessToken, $soapXml, $response);
                        if (isset($responseArray['soapenvBody']['trProcessMessageResponse']['trpayload']['trcontent']['_5ConfirmBOD']['_5ConfirmBODDataArea']['_5BOD']['_9BODSuccessMessage']['_9WarningProcessMessage'])) {
                            $this->addWarning($api_url, $accessToken, $soapXml, $response);
                        }
                        
                    } else {
                        $this->addError($api_url, $accessToken, $soapXml, $response);
                    }
                }
                curl_close($curl);
            } catch (\Exception $e) {
                $this->addError($api_url, $accessToken, $soapXml, $e->getMessage());
            }
        }
    }
    /**
     * Test connection
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        return true;
    }
    /**
     * Add Xtento error log
     *
     * @param string $api_url
     * @param string $accessToken
     * @param string $soapXml
     * @param string $response
     */
    public function addError(string $api_url, string $accessToken, string $soapXml, string $response): void
    {
        $this->backendSession->setDDOAError(1);
        $logEntry = $this->_registry->registry('orderexport_log');
        $logEntry->setResult(\Xtento\OrderExport\Model\Log::RESULT_FAILED);
        $logEntry->addResultMessage(__($response));
        $request = $this->getCurlRequest($api_url, $accessToken, $soapXml);
        $this->ddoaLogger->error('DDOA Request: ' . $request);
        $this->ddoaLogger->error('DDOA Response: ' . $response);
    }

    /**
     * Add Xtento warning log
     *
     * @param string $api_url
     * @param string $accessToken
     * @param string $soapXml
     * @param string $response
     */
    public function addWarning(string $api_url, string $accessToken, string $soapXml, string $response): void
    {
        $logEntry = $this->_registry->registry('orderexport_log');
        $logEntry->setResult(\Xtento\OrderExport\Model\Log::RESULT_WARNING);
        $logEntry->addResultMessage(__($response));
        $request = $this->getCurlRequest($api_url, $accessToken, $soapXml);
        $this->ddoaLogger->warning('DDOA Request: ' . $request);
        $this->ddoaLogger->warning('DDOA Response: ' . $response);
    }
    /**
     * Add Xtento success log
     *
     * @param string $api_url
     * @param string $accessToken
     * @param string $soapXml
     * @param string $response
     */
    public function addSuccess(string $api_url, string $accessToken, string $soapXml, string $response): void
    {
        $logEntry = $this->_registry->registry('orderexport_log');
        $logEntry->setResult(\Xtento\OrderExport\Model\Log::RESULT_SUCCESSFUL);
        $logEntry->addResultMessage(__($response));        
        $request = $this->getCurlRequest($api_url, $accessToken, $soapXml);
        $this->ddoaLogger->info('DDOA Request: ' . $request);
        $this->ddoaLogger->info('DDOA Response: ' . $response);
    }
    /**
     * Add DDOA curl request log
     *
     * @param string $url
     * @param string $token
     * @param string $soapXml
     */
    public function getCurlRequest($url, $token, $soapXml): string
    {        
        $request = "curl --location --request POST '" . $url . "' --header 'Authorization: Bearer " . $token . "' --header 'Content-Type: application/xml' --data-raw '" . $soapXml . "'";
        return $request;
    }
}
