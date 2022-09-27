<?php
/**
 * @package Infosys/CustomerSSO
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\CustomerSSO\Model\Resolver;
 
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Infosys\CustomerSSO\Model\DCS;

/**
 * Activate customer GraphQL request processing
 */
class ActivateCustomerResolver implements ResolverInterface
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $customerCollection;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var DCS
     */
    protected $dcs;

    /**
     * Constuctor function
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection
     * @param \Psr\Log\LoggerInterface $logger
     * @param DCS $dcs
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection,
        \Psr\Log\LoggerInterface $logger,
        DCS $dcs,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->customerRepository = $customerRepository;
        $this->customerCollection = $customerCollection;
        $this->_logger = $logger;
        $this->dcs = $dcs;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (empty($args['activationCode'])) {
            throw new GraphQlInputException(__('"Activation code" should be specified'));
        }

        $activation_code = $args['activationCode'];

        //activate customer api call
        $result = $this->dcs->activateCustomerEmail($activation_code);
        
        if (isset($result['message'])) {
            $output = ['message' => $result['message']];
        } else {
            if(isset($result['tmnaGuid'])){
                //update customer email in magento
                $guid = $result['tmnaGuid'];
                $new_email = $result['email'];
                $collection = $this->customerCollection->addAttributeToSelect('*')
                            ->addAttributeToFilter('dcs_guid', $guid)
                            ->load();
                        
                $customer_data = $collection->getData();
                $customer_email = $customer_data[0]['email'];
                $customer = $this->customerRepository->get($customer_email);
                $customer->setEmail($new_email);
                $this->customerRepository->save($customer);
            }
             
            $output = ['message' => $result['status']['messages'][0]['description']];
        }

        return $output;
    }
    
    /**
     * Get store config value
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
}
