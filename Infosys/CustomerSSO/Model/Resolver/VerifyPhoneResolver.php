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
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Infosys\CustomerSSO\Model\DCS;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Update Customer GraphQL request processing
 */
class VerifyPhoneResolver implements ResolverInterface
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
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * Constuctor function
     *
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param DCS $dcs
     * @param GetCustomer $getCustomer
     * @param ScopeConfigInterface $scopeConfig
     * @param SessionManagerInterface $session
     * @param Emulation $emulation
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Psr\Log\LoggerInterface $logger,
        DCS $dcs,
        GetCustomer $getCustomer,
        ScopeConfigInterface $scopeConfig,
        SessionManagerInterface $session,
        \Magento\Store\Model\App\Emulation $emulation
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->customerRepository = $customerRepository;
        $this->_logger = $logger;
        $this->dcs = $dcs;
        $this->getCustomer = $getCustomer;
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->emulation = $emulation;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (empty($args['otpCode'])) {
            throw new GraphQlInputException(__('"OTP" should be specified'));
        }
        
        $customer = $this->getCustomer->execute($context);
        $email = $customer->getEmail();
        $phone = $this->session->getPhone();
        
        $otpCode = $args['otpCode'];
        //update customer api call
        $result = $this->dcs->verifyCustomerPhone($otpCode);
        if (isset($result['message'])) {
            $output = ['message' => $result['message']];
        } else {
            $output = ['message' => $result['status']['messages'][0]['description']];
        }
        if ($output['message'] == 'REQUEST_COMPLETED_SUCCESSFULLY') {
            $customer = $this->customerRepository->get($email);
            // Set the store id and area
            $this->emulation->startEnvironmentEmulation(
                $customer->getStoreId(),
                \Magento\Framework\App\Area::AREA_FRONTEND,
                true
            );
            $customer->setCustomAttribute('phone_number', $phone);
            $this->customerRepository->save($customer);
            $this->emulation->stopEnvironmentEmulation();
        }
        $this->session->unsPhone();
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
