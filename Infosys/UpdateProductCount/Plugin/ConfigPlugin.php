<?php

namespace Infosys\UpdateProductCount\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Client\Curl;

class ConfigPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

     /**
      * @var Logger
      */
    protected $logger;
    
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @param Logger $logger
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Logger $logger,
        Curl $curl,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
    }

    public function aroundSave(
        \Magento\Config\Model\Config $subject,
        \Closure $proceed
    ) {
        $pageSize = $this->scopeConfig->getValue(
            'updatecount/general/maximum_product_count',
            ScopeInterface::SCOPE_STORE
        );

        $elasticHost = $this->scopeConfig->getValue(
            'catalog/search/elasticsearch7_server_hostname',
            ScopeInterface::SCOPE_STORE
        );

        $elasticPort = $this->scopeConfig->getValue(
            'catalog/search/elasticsearch7_server_port',
            ScopeInterface::SCOPE_STORE
        );

        $URL = "http://".$elasticHost.":".$elasticPort."/_settings";

        //set curl options
        $this->curl->setOption(CURLOPT_HEADER, 0);
        $this->curl->setOption(CURLOPT_TIMEOUT, 60);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->curl->setOption(CURLOPT_POSTFIELDS, '{ "index" : { "max_result_window" : <?= $pageSize ?> } }');
        //set curl header
        $this->curl->addHeader("Content-Type", "application/json");
        try {
            //get request with url
            $this->curl->get($URL);
            //read response
            $response = $this->curl->getBody();
        } catch (\Exception $e) {
            $this->logger->error('Elastic search settings issue' . $e->getMessage());
        }

        return $proceed();
    }
}
