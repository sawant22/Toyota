<?php

/**
 * @package   Infosys/CustomerSSO
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerSSO\Controller\Saml2;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as CollectionRegionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CollectionCountryFactory;
use Magento\Directory\Model\RegionFactory;
use Psr\Log\LoggerInterface;
use Pitbulk\SAML2\Controller\AbstractCustomController;
use Pitbulk\SAML2\Helper\Data;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Store\Model\StoreManagerInterface;
use Infosys\CustomerSSO\Helper\Data as SsoHelper;

class ACS extends AbstractCustomController implements HttpPostActionInterface
{
    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CollectionRegionFactory
     */
    private $regionCollectionFactory;

    /**
     * @var CollectionCountryFactory
     */
    private $countryCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;
    /**
     * @var TokenModelFactory
     */
    protected $tokenFactory;
    /**
     * @var ssoHelper
     */
    protected $ssoHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * Constructor function
     *
     * @param Context $context
     * @param Session $session
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param FormKey $formKey
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressInterfaceFactory $addressFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param CollectionRegionFactory $regionCollectionFactory
     * @param CollectionCountryFactory $countryCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RegionFactory $regionFactory
     * @param TokenModelFactory $tokenFactory
     */
    public function __construct(
        Context $context,
        Session $session,
        Data $helper,
        LoggerInterface $logger,
        FormKey $formKey,
        AccountManagementInterface $customerAccountManagement,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressInterfaceFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        CollectionRegionFactory $regionCollectionFactory,
        CollectionCountryFactory $countryCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RegionFactory $regionFactory,
        TokenModelFactory $tokenFactory,
        SsoHelper $ssoHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->regionFactory = $regionFactory;
        $this->tokenFactory = $tokenFactory;
        $this->ssoHelper = $ssoHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;

        parent::__construct($context, $session, $helper, $logger, $formKey);
    }
    /**
     * SSO Response
     *
     * @return void
     */
    public function execute()
    {
        $urlToGo = '/';

        $customerSession = $this->_getCustomerSession();
        $storeId = $this->storeManager->getStore()->getId();
        $redirectTo =  $this->ssoHelper->getSsoRedirectionUrl($storeId);

        $helper = $this->_getHelper();

        // Prevent if already logged
        if ($customerSession->isLoggedIn()) {
            $this->_redirect($redirectTo);
            return;
        }

        $moduleEnabled = $helper->checkEnabledModule();
        if (!$moduleEnabled) {
            $this->processError('SAML module has disabled status');
            return;
        }

        $auth = $this->_getSAMLAuth();
        $auth->processResponse();
        $errors = $auth->getErrors();
        if (!empty($errors)) {
            $errorMsg = "Error at the ACS Endpoint.<br>" . implode(', ', $errors);
            $debug = $helper->getConfig('pitbulk_saml2_customer/advanced/debug');
            $reason = $auth->getLastErrorReason();
            if ($debug && isset($reason) && !empty($reason)) {
                $errorMsg .= '<br><br>Reason: ' . $reason;
            }

            $this->processError($errorMsg);
            return;
        } elseif (!$auth->isAuthenticated()) {
            $this->processError("ACS Process failed");
            return;
        }

        $useCustomAttr = $helper->getConfig('pitbulk_saml2_customer/custom_field_mapping/use_custom_to_identity_user');
        $customerData = $this->processAttrs($auth, $useCustomAttr);
        if ($useCustomAttr) {
            if (empty($customerData['custom_attr']) || empty(reset($customerData['custom_attr']))) {
                $errorMsg = "SAML plugin can't obtain the custom value" .
                    " used to identify the user from the SAML Response. Review the data sent by your IdP";
                $this->processError($errorMsg);
                return;
            }
        } elseif (empty($customerData['email'])) {
            $errorMsg = "SAML plugin can't obtain the email" .
                " value from the SAML Response. Review the" .
                " data sent by your IdP and the Attribute" .
                " Mapping setting options";
            $this->processError($errorMsg);
            return;
        }

        $blackListedEmails = explode(
            ",",
            preg_replace(
                '/\s+/',
                '',
                $helper->getConfig('pitbulk_saml2_customer/protect_options/blacklisted_emails')
            )
        );
        if (!empty($blackListedEmails)) {
            if (in_array($customerData['email'], $blackListedEmails)) {
                $errorMsg = $customerData['email'] . " not authorized to login via SAML IdP";
                $this->processError($errorMsg);
                return;
            }
        }

        try {
            $customer = null;
            if ($useCustomAttr) {
                $value = reset($customerData['custom_attr']);
                $key = key($customerData['custom_attr']);
                $searchCriteria = $helper->getConfig('pitbulk_saml2_customer/custom_field_mapping/search_criteria');
                if (empty($searchCriteria)) {
                    $searchCriteria = 'LIKE';
                }
                $this->searchCriteriaBuilder->addFilter($key, $value, $searchCriteria);
                $searchCriteria = $this->searchCriteriaBuilder->create();
                $list = $this->customerRepository->getList($searchCriteria);
                if ($list->getTotalCount() > 0) {
                    foreach ($list->getItems() as $item) {
                        $customer = $item;
                        break;
                    }
                } else {
                    $customer = $this->customerRepository->get($customerData['email']);
                }
            } else {
                $customer = $this->customerRepository->get($customerData['email']);
            }

            $this->_eventManager->dispatch(
                'pitbulk_saml2_customer_check',
                ['customer' => $customer, 'customerData' => $customerData, 'samlAuth' => $auth]
            );

            if (!isset($customer)) {
                throw new NoSuchEntityException();
            }

            // Customer exists
            $customer = $this->updateCustomer($customer, $customerData, $useCustomAttr);
            $this->_eventManager->dispatch(
                'pitbulk_saml2_customer_successfully_updated',
                ['customer' => $customer, 'customerData' => $customerData, 'samlAuth' => $auth]
            );
        } catch (NoSuchEntityException $e) {
            // Customer doesn't exist
            $customer = $this->provisionCustomer($customerData);

            $this->_eventManager->dispatch(
                'pitbulk_saml2_customer_successfully_created',
                ['customer' => $customer, 'customerData' => $customerData, 'samlAuth' => $auth]
            );

            if (empty($customerData['address'])) {
                $forceAddress = $helper->getConfig('pitbulk_saml2_customer/protect_options/forceaddressprovisioning');
                if ($forceAddress != false) {
                    $urlToGo = 'customer/address/new';
                }
            }
        }

        return $this->tryLogAndRedirect($customer, $customerSession, $auth, $urlToGo);
    }
    /**
     * Try log and redirect properly
     *
     * @param object $customer
     * @param object $customerSession
     * @param object $auth
     * @param string $urlToGo
     * @return void
     */
    private function tryLogAndRedirect($customer, $customerSession, $auth, $urlToGo)
    {
        $helper = $this->_getHelper();

        if (isset($customer)) {
            $this->registerCustomerSession($customerSession, $auth, $customer);

            // Update last customer id, if required
            $lastCustomerId = $customerSession->getLastCustomerId();
            if (
                isset($lastCustomerId) && $customerSession->isLoggedIn() &&
                $lastCustomerId != $customerSession->getId()
            ) {
                $customerSession->unsBeforeAuthUrl()
                    ->setLastCustomerId($customerSession->getId());
            }

            $relayState = $this->getRequest()->getPost('RelayState');
            if (
                !empty($relayState) &&
                $urlToGo == '/' && $helper->isRelayStateWhitelisted($relayState) &&
                strpos($relayState, 'logout') === false
            ) {
                // Expects as $urlToGo an URL
                $destination = $relayState;
            } else {
                $destination = $helper->getUrl($urlToGo);
            }

            $sessionId = $customerSession->getSessionId();

            $destination = $this->getDestination($destination);

            $this->_view->loadLayout();
            $this->_view->getLayout()->unsetElement('sso_saml2_acs');
            $block = $this->_view->getLayout()->getBlock('sso_saml2_login_postback');
            $block->setData('action', $destination);
            $block->setData('sessionId', $sessionId);
            $this->_view->renderLayout();
            return;
        } else {
            $errorMsg = "SAML plugin failed trying to process the SSO. Review the Attribute Mapping section";
            $this->processError($errorMsg);
            return;
        }
    }

    /**
     * Returns input action with /jcr:content.login before any url parameters
     *
     * @param String $destination
     * @return String
     */
    private function getDestination($destination)
    {
        $destinations = explode("?", $destination);
        $destination = rtrim($destinations[0], "/") . "/jcr:content.login";
        if (array_key_exists(1, $destinations)) {
            $destination .= "?" . $destinations[1];
        }
        return $destination;
    }

    /**
     * Provision customer
     *
     * @param array $customerData
     * @return void
     */
    private function provisionCustomer($customerData)
    {
        $helper = $this->_getHelper();
        $autocreate = $helper->getConfig('pitbulk_saml2_customer/options/autocreate');
        if ($autocreate) {
            try {
                $customerEntity = $this->customerFactory->create();
                if (!empty($customerData['username'])) {
                    $customerEntity->setCustomAttribute('username', $customerData['username']);
                }
                // Custom Attr
                if (isset($customerData['custom_attr'])) {
                    foreach ($customerData['custom_attr'] as $key => $value) {
                        $customerEntity->setCustomAttribute($key, $value);
                    }
                }
                $customerEntity->setEmail($customerData['email']);
                $customerEntity->setFirstname($customerData['firstname']);
                $customerEntity->setLastname($customerData['lastname']);
                if (empty($customerData['groupid'])) {
                    $defaultGroup = $helper->getConfig('pitbulk_saml2_customer/options/defaultgroup');
                    if (!empty($defaultGroup)) {
                        $customerData['groupid'] = $defaultGroup;
                    } else {
                        $customerData['groupid'] = $helper->getConfig('customer/create_account/default_group');
                    }
                }
                $customerEntity->setGroupId($customerData['groupid']);
                $customer = $this->customerAccountManagement
                    ->createAccount($customerEntity);

                if (!empty($customerData['address'])) {
                    $this->provisionAddress($customerData, $customer->getId());
                }

                $this->_eventManager->dispatch(
                    'customer_register_success',
                    ['account_controller' => $this, 'customer' => $customer]
                );
                $successMsg = __('Customer registration successful.');
                $this->messageManager->addSuccess($successMsg);
                return $customer;
            } catch (Exception $e) {
                $errorMsg = 'The auto-provisioning process failed: ' .
                    $e->getMessage();
            }
        } else {
            $customer_identifier = '';
            if (isset($customerData['email'])) {
                $customer_identifier = $customerData['email'];
            }
            $errorMsg = 'The login could not be completed, customer ' . $customer_identifier .
                ' does not exist in Magento and the auto-provisioning' .
                ' function is disabled';
        }
        $this->processError($errorMsg);
    }
    /**
     * Provision customer address
     *
     * @param array $customerData
     * @param int $customerId
     * @return void
     */
    private function provisionAddress($customerData, $customerId)
    {
        if ((isset($customerData['firstname']) && !empty($customerData['firstname'])) &&
            (isset($customerData['lastname']) && !empty($customerData['lastname'])) &&
            (isset($customerData['address']['telephone']) && !empty($customerData['address']['telephone'])) &&
            (isset($customerData['address']['street']) && !empty($customerData['address']['street'])) &&
            (isset($customerData['address']['city']) && !empty($customerData['address']['city'])) &&
            (isset($customerData['address']['country_code']) && !empty($customerData['address']['country_code'])) &&
            (isset($customerData['address']['postcode']) && !empty($customerData['address']['postcode']))
        ) {
            $address = $this->addressFactory->create();
            $address->setFirstname($customerData['firstname'])
                ->setLastname($customerData['lastname'])
                ->setCustomerId($customerId)
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1');

            if (isset($customerData['address']['street'])) {
                $address->setStreet($customerData['address']['street']);
            }

            if (isset($customerData['address']['company'])) {
                $address->setCompany($customerData['address']['company']);
            }

            if (isset($customerData['address']['city'])) {
                $address->setCity($customerData['address']['city']);
            }

            $address = $this->handleCountryAndRegion($customerData, $address);

            if (isset($customerData['address']['postcode'])) {
                $address->setPostcode($customerData['address']['postcode']);
            }

            if (isset($customerData['address']['telephone'])) {
                $address->setTelephone($customerData['address']['telephone']);
            }

            if (isset($customerData['address']['fax'])) {
                $address->setFax($customerData['address']['fax']);
            }

            $this->saveAddress($address);
        }
    }
    /**
     * Provision customer address region
     *
     * @param array $customerData
     * @param object $address
     * @return object
     */
    private function handleCountryAndRegion($customerData, $address)
    {
        $regionRequired = false;
        $country_id = null;
        if (isset($customerData['address']['country_code'])) {
            $country_code = $customerData['address']['country_code'];

            $countries = $this->countryCollectionFactory->create()
                ->addCountryCodeFilter($country_code)
                ->loadData()
                ->toOptionArray(false);

            if (empty($countries)) {
                $countryId = $this->getCountryId($country_code);
                if (isset($countryId)) {
                    $countries = $this->countryCollectionFactory->create()
                        ->addCountryIdFilter($countryId)
                        ->loadData()
                        ->toOptionArray(false);
                }
            }

            if (!empty($countries)) {
                $country = $countries[0];
                $country_id = $country['value'];
                $address->setCountryId($country_id);
                // Need review
                if (isset($country['is_region_required'])) {
                    $regionRequired = $country['is_region_required'];
                } elseif (isset($country['is_region_visible'])) {
                    $regionRequired = $country['is_region_visible'];
                }
            }
        }

        if (isset($customerData['address']['region'])) {
            $regionData = $customerData['address']['region'];
            $address = $this->provisionRegion($address, $regionData, $regionRequired, $country_id);
        }

        return $address;
    }
    /**
     * Save customer address
     *
     * @param object $address
     * @return void
     */
    private function saveAddress($address)
    {
        try {
            $this->addressRepository->save($address);
            $this->messageManager->addSuccess(__('Customer address added.'));
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Customer address can\'t be saved.'));
        }
    }
    /**
     * Provision customer region
     *
     * @param object $address
     * @param string $regionData
     * @param string $regionRequired
     * @param int $country_id
     * @return void
     */
    private function provisionRegion($address, $regionData, $regionRequired, $country_id)
    {
        if ($regionRequired) {
            $regionModel = $this->regionFactory->create();

            $region = $regionModel->loadByCode($regionData, $country_id);
            $region_id = $region->getId();
            if (!isset($region_id)) {
                $region = $regionModel->loadByName($regionData, $country_id);
                $region_id = $region->getId();
            }

            if (isset($region_id)) {
                $address->setRegionId($region_id);
            }
        } else {
            $regions = $this->regionCollectionFactory->create()
                ->addRegionCodeOrNameFilter($regionData)
                ->loadData()
                ->toOptionArray();

            if (!empty($regions)) {
                // 1st region is on position 1
                $address->setRegionId($regions[1]['value']);
                $address->setCountryId($regions[1]['country_id']);
            }
        }

        return $address;
    }
    /**
     * Update customer
     *
     * @param object $customer
     * @param array $customerData
     * @param string $useCustomAttr
     * @return void
     */
    private function updateCustomer($customer, $customerData, $useCustomAttr)
    {
        $helper = $this->_getHelper();
        $updateCustomer = $helper->getConfig('pitbulk_saml2_customer/options/updateuser');
        if ($updateCustomer) {
            if (!empty($customerData['firstname'])) {
                $customer->setFirstname($customerData['firstname']);
            }
            if (!empty($customerData['lastname'])) {
                $customer->setLastname($customerData['lastname']);
            }
            if (!empty($customerData['groupid'])) {
                $customer->setGroupId($customerData['groupid']);
            }

            if ($useCustomAttr) {
                if (!empty($customerData['email'])) {
                    $customer->setEmail($customerData['email']);
                }

                $customAttrIdentifier =  $helper->getConfig(
                    'pitbulk_saml2_customer/custom_field_mapping/custom_attribute_mapping'
                );
                if (isset($customerData['custom_attr'][$customAttrIdentifier])) {
                    unset($customerData['custom_attr'][$customAttrIdentifier]);
                }
            }

            if (isset($customerData['custom_attr'])) {
                // Custom Attr
                foreach ($customerData['custom_attr'] as $key => $value) {
                    $customer->setCustomAttribute($key, $value);
                }
            }
            $customer = $this->customerRepository->save($customer);
        }

        // If there is no address registered and SAMLResponse contains
        // address info, try register it
        if (!empty($customerData['address'])) {
            $addresses = $customer->getAddresses();
            if (empty($addresses)) {
                $this->provisionAddress($customerData, $customer->getId());
            }
        }

        return $customer;
    }
    /**
     * Register customer session
     *
     * @param object $customerSession
     * @param object $auth
     * @param object $customer
     * @return void
     */
    private function registerCustomerSession($customerSession, $auth, $customer)
    {
        $customerSession->setCustomerDataAsLoggedIn($customer);

        $customerSession->setData('saml_login', true);
        $nameId = $auth->getNameId();
        $customerSession->setData('saml_nameid', $nameId);
        $nameIdFormat = $auth->getNameIdFormat();
        $customerSession->setData('saml_nameid_format', $nameIdFormat);
        $sessionIndex = $auth->getSessionIndex();
        $customerSession->setData('saml_sessionindex', $sessionIndex);
        $nameIdNameQualifier = $auth->getNameIdNameQualifier();
        $customerSession->setData('saml_nameid_nq', $nameIdNameQualifier);
        $nameIdSPNameQualifier = $auth->getNameIdSPNameQualifier();
        $customerSession->setData('saml_nameid_spnq', $nameIdSPNameQualifier);

        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        }
    }
    /**
     * Process SSO attributes
     *
     * @param object $auth
     * @param boolean $useCustomAttr
     * @return array
     */
    public function processAttrs($auth, $useCustomAttr = false)
    {
        $customerData = [
            'username' => '',
            'email' => '',
            'firstname' => '',
            'lastname' => '',
            'groupid' => ''
        ];

        $attrs = $auth->getAttributes();

        if (empty($attrs)) {
            if (!$useCustomAttr) {
                $customerData['email'] = $auth->getNameId();
            } else {
                $helper = $this->_getHelper();
                $customAttrIdentifier =  $helper->getConfig(
                    'pitbulk_saml2_customer/custom_field_mapping/custom_attribute_mapping'
                );
                if (!empty($customAttrIdentifier)) {
                    $customerData['custom_attr'] = [
                        $customAttrIdentifier => $auth->getNameId()
                    ];
                }
            }
        } else {
            $mapping = $this->getAttrMapping();
            foreach (['username', 'email', 'firstname', 'lastname'] as $key) {
                if (!empty($mapping[$key])) {
                    $mapped_keys = explode(",", trim($mapping[$key]));
                    foreach ($mapped_keys as $mapped_key) {
                        if (isset($attrs[$mapped_key]) && !empty($attrs[$mapped_key][0])) {
                            $customerData[$key] = trim($attrs[$mapped_key][0]);
                        }
                    }
                }
            }

            $customerData = $this->addGroupData($customerData, $attrs, $mapping);

            $customerData = $this->addAddressData($customerData, $attrs, $mapping);

            $customerData = $this->addCustomAttributesData($customerData, $attrs);

            // If was not able to get the email by mapping,
            // assign then the NameId if it contains an @
            if (!isset($customerData['email']) || empty($customerData['email'])) {
                $nameId = $auth->getNameId();
                if (strpos($nameId, "@") !== false) {
                    $customerData['email'] = $nameId;
                }
            }
        }

        return $customerData;
    }

    /**
     * Aux method for assign group
     *
     * @param array $customerData
     * @param array $attrs
     * @param array $mapping
     * @return array
     */
    private function addGroupData($customerData, $attrs, $mapping)
    {
        if (!empty($mapping['group'])) {
            $mapped_keys = explode(",", $mapping['group']);
            $groupValues = [];
            foreach ($mapped_keys as $mapped_key) {
                if (isset($attrs[$mapped_key]) && !empty($attrs[$mapped_key])) {
                    $groupValues = array_merge($groupValues, $attrs[$mapped_key]);
                }
            }

            $groupValues = array_unique($groupValues);
            if (!empty($groupValues)) {
                $groupid = $this->calculateGroupId($groupValues);

                if ($groupid !== false) {
                    $customerData['groupid'] = $groupid;
                }
            }
        }

        return $customerData;
    }
    /**
     * Map customer group
     *
     * @param string $groupValues
     * @return string
     */
    private function calculateGroupId($groupValues)
    {
        $helper = $this->_getHelper();

        $groupMappingMode = $helper->getConfig('pitbulk_saml2_customer/options/groupmappingmode');
        if (empty($groupMappingMode) || $groupMappingMode == "mapping_mode") {
            $groupMapping = $this->getGroupMapping();
            $groupid = $this->obtainGroupId($groupValues, $groupMapping);
        } else {
            $groupid = false;
            if ($groupMappingMode == "name_mode") {
                $availableGroups = $helper->getAvailableGroups('name');
                foreach ($groupValues as $groupValue) {
                    if (isset($availableGroups[$groupValue])) {
                        $groupid = $availableGroups[$groupValue];
                        break;
                    }
                }
            } else {
                $availableGroups = $helper->getAvailableGroups('id');
                foreach ($groupValues as $groupValue) {
                    if (isset($availableGroups[$groupValue])) {
                        $groupid = $groupValue;
                        break;
                    }
                }
            }
        }
        return $groupid;
    }

    /**
     * Aux method for assign address
     *
     * @param array $customerData
     * @param array $attrs
     * @param array $mapping
     * @return array
     */
    private function addAddressData($customerData, $attrs, $mapping)
    {
        $customerData['address'] = [];

        if (isset($mapping['address'])) {
            foreach ($mapping['address'] as $key => $map) {
                if (
                    empty($map) || !isset($attrs[$map])
                    || empty($attrs[$map][0])
                ) {
                    continue;
                }

                if ($key == 'street1') {
                    $customerData['address']['street'][0] = trim($attrs[$map][0]);
                } elseif ($key == 'street2') {
                    $customerData['address']['street'][1] = trim($attrs[$map][0]);
                } else {
                    $customerData['address'][$key] = trim($attrs[$map][0]);
                }
            }
        }

        return $customerData;
    }
    /**
     * Aux method for assign custom attributes
     *
     * @param array $customerData
     * @param array $attrs
     * @return array
     */
    private function addCustomAttributesData($customerData, $attrs)
    {
        $customMapping = $this->getCustomMapping();
        if (!empty($customMapping)) {
            $customerData['custom_attr'] = [];
            foreach ($customMapping as $key => $map) {
                if (isset($attrs[$map])) {
                    $customerData['custom_attr'][$key] = trim($attrs[$map][0]);
                }
            }
            if (empty($customerData['custom_attr'])) {
                unset($customerData['custom_attr']);
            }
        }

        return $customerData;
    }
    /**
     * Aux method for get group mapping
     *
     * @return string
     */
    private function getGroupMapping()
    {
        $helper = $this->_getHelper();

        $groupMapping = [];
        for ($i = 1; $i < 26; $i++) {
            $key = 'pitbulk_saml2_customer/group_mapping/group' . $i;
            $maps = $helper->getConfig($key);
            $groupMapping[$i] = explode(',', trim($maps));
        }

        return $groupMapping;
    }

    /**
     * Aux method for get attribute mapping
     *
     * @return array
     */
    private function getAttrMapping()
    {
        $helper = $this->_getHelper();
        $mapping = [];

        $attrMapKey = 'pitbulk_saml2_customer/attr_mapping/';
        $addrMapKey = 'pitbulk_saml2_customer/address_mapping/';

        $mapping['username'] =  $helper->getConfig($attrMapKey . 'username');
        $mapping['email'] =  $helper->getConfig($attrMapKey . 'email');
        $mapping['firstname'] =  $helper->getConfig($attrMapKey . 'firstname');
        $mapping['lastname'] =  $helper->getConfig($attrMapKey . 'lastname');
        $mapping['group'] = $helper->getConfig($attrMapKey . 'group');

        $addrMap = [];
        $addrMap['company'] = $helper->getConfig($addrMapKey . 'company');
        $addrMap['street1'] = $helper->getConfig($addrMapKey . 'street1');
        $addrMap['street2'] = $helper->getConfig($addrMapKey . 'street2');
        $addrMap['city'] = $helper->getConfig($addrMapKey . 'city');
        $addrMap['country_code'] = $helper->getConfig($addrMapKey . 'country');
        $addrMap['region'] = $helper->getConfig($addrMapKey . 'state');
        $addrMap['postcode'] = $helper->getConfig($addrMapKey . 'zip');
        $addrMap['telephone'] = $helper->getConfig($addrMapKey . 'telephone');
        $addrMap['fax'] = $helper->getConfig($addrMapKey . 'fax');

        $mapping['address'] = $addrMap;

        return $mapping;
    }

    /**
     * Aux method for calculating groupid
     *
     * @param array $samlGroups
     * @param array $groupValues
     * @return string
     */
    private function obtainGroupId($samlGroups, $groupValues)
    {
        foreach ($samlGroups as $samlGroup) {
            for ($i = 1; $i < 26; $i++) {
                if (in_array($samlGroup, $groupValues[$i])) {
                    return $i;
                }
            }
        }
        return false;
    }
    /**
     * Get Customer Id
     *
     * @param string $countryName
     * @return void
     */
    private function getCountryId($countryName)
    {
        $countries = [
            "Afghanistan" => "AF",
            "Åland Islands" => "AX",
            "Albania" => "AL",
            "Algeria" => "DZ",
            "American Samoa" => "AS",
            "Andorra" => "AD",
            "Angola" => "AO",
            "Anguilla" => "AI",
            "Antarctica" => "AQ",
            "Antigua and Barbuda",
            "Argentina" => "AR",
            "Armenia" => "AM",
            "Aruba" => "AW",
            "Australia" => "AU",
            "Austria" => "AT",
            "Azerbaijan" => "AZ",
            "Bahamas" => "BS",
            "Bahrain" => "BH",
            "Bangladesh" => "BD",
            "Barbados" => "BB",
            "Belarus" => "BY",
            "Belgium" => "BE",
            "Belize" => "BZ",
            "Benin" => "BJ",
            "Bermuda" => "BM",
            "Bhutan" => "BT",
            "Bolivia" => "BO",
            "Bosnia and Herzegovina" => "BA",
            "Botswana" => "BW",
            "Bouvet Island" => "BV",
            "Brazil" => "BR",
            "British Indian Ocean Territory" => "IO",
            "British Virgin Islands" => "VG",
            "Brunei" => "BN",
            "Bulgaria" => "BG",
            "Burkina Faso" => "BF",
            "Burundi" => "BI",
            "Cambodia" => "KH",
            "Cameroon" => "CM",
            "Canada" => "CA",
            "Cape Verde" => "CV",
            "Cayman Islands" => "KY",
            "Central African Republic" => "CF",
            "Chad" => "TD",
            "Chile" => "CL",
            "China" => "CN",
            "Christmas Island" => "CX",
            "Cocos (Keeling) Islands" => "CC",
            "Colombia" => "CO",
            "Comoros" => "KM",
            "Congo - Brazzaville" => "CG",
            "Congo - Kinshasa" => "CD",
            "Cook Islands" => "CK",
            "Costa Rica" => "CR",
            "Côte d’Ivoire" => "CI",
            "Croatia" => "HR",
            "Cuba" => "CU",
            "Cyprus" => "CY",
            "Czech Republic" => "CZ",
            "Denmark" => "DK",
            "Djibouti" => "DJ",
            "Dominica" => "DM",
            "Dominican Republic" => "DO",
            "Ecuador" => "EC",
            "Egypt" => "EG",
            "El Salvador" => "SV",
            "Equatorial Guinea" => "GQ",
            "Eritrea" => "ER",
            "Estonia" => "EE",
            "Ethiopia" => "ET",
            "Falkland Islands" => "FK",
            "Faroe Islands" => "FO",
            "Fiji" => "FJ",
            "Finland" => "FI",
            "France" => "FR",
            "French Guiana" => "GF",
            "French Polynesia" => "PF",
            "French Southern Territories" => "TF",
            "Gabon" => "GA",
            "Gambia" => "GM",
            "Georgia" => "GE",
            "Germany" => "DE",
            "Ghana" => "GH",
            "Gibraltar" => "GI",
            "Greece" => "GR",
            "Greenland" => "GL",
            "Grenada" => "GD",
            "Guadeloupe" => "GP",
            "Guam" => "GU",
            "Guatemala" => "GT",
            "Guernsey" => "GG",
            "Guinea" => "GN",
            "Guinea-Bissau" => "GW",
            "Guyana" => "GY",
            "Haiti" => "HT",
            "Honduras" => "HN",
            "Hong Kong SAR China" => "HK",
            "Hungary" => "HU",
            "Iceland" => "IS",
            "India" => "IN",
            "Indonesia" => "ID",
            "Iran" => "IR",
            "Iraq" => "IQ",
            "Ireland" => "IE",
            "Isle of Man" => "IM",
            "Israel" => "IL",
            "Italy" => "IT",
            "Jamaica" => "JM",
            "Japan" => "JP",
            "Jersey" => "JE",
            "Jordan" => "JO",
            "Kazakhstan" => "KZ",
            "Kenya" => "KE",
            "Kiribati" => "KI",
            "Kuwait" => "KW",
            "Kyrgyzstan" => "KG",
            "Laos" => "LA",
            "Latvia" => "LV",
            "Lebanon" => "LB",
            "Lesotho" => "LS",
            "Liberia" => "LR",
            "Libya" => "LY",
            "Liechtenstein" => "LI",
            "Lithuania" => "LT",
            "Luxembourg" => "LU",
            "Macau SAR China" => "MO",
            "Macedonia" => "MK",
            "Madagascar" => "MG",
            "Malawi" => "MW",
            "Malaysia" => "MY",
            "Maldives" => "MV",
            "Mali" => "ML",
            "Malta" => "MT",
            "Marshall Islands" => "MH",
            "Martinique" => "MQ",
            "Mauritania" => "MR",
            "Mauritius" => "MU",
            "Mayotte" => "YT",
            "Mexico" => "MX",
            "Micronesia" => "FM",
            "Moldova" => "MD",
            "Monaco" => "MC",
            "Mongolia" => "MN",
            "Montenegro" => "ME",
            "Montserrat" => "MS",
            "Morocco" => "MA",
            "Mozambique" => "MZ",
            "Myanmar (Burma)" => "MM",
            "Namibia" => "NA",
            "Nauru" => "NR",
            "Nepal" => "NP",
            "Netherlands" => "NL",
            "Netherlands Antilles" => "AN",
            "New Caledonia" => "NC",
            "New Zealand" => "NZ",
            "Nicaragua" => "NI",
            "Niger" => "NE",
            "Nigeria" => "NG",
            "Niue" => "NU",
            "Norfolk Island" => "NF",
            "Northern Mariana Islands" => "MP",
            "North Korea" => "KP",
            "Norway" => "NO",
            "Oman" => "OM",
            "Pakistan" => "PK",
            "Palau" => "PW",
            "Palestinian Territories" => "PS",
            "Panama" => "PA",
            "Papua New Guinea" => "PG",
            "Paraguay" => "PY",
            "Peru" => "PE",
            "Philippines" => "PH",
            "Pitcairn Islands" => "PN",
            "Poland" => "PL",
            "Portugal" => "PT",
            "Qatar" => "QA",
            "Réunion" => "RE",
            "Romania" => "RO",
            "Russia" => "RU",
            "Rwanda" => "RW",
            "Saint Barthélemy" => "BL",
            "Saint Helena" => "SH",
            "Saint Kitts and Nevis" => "KN",
            "Saint Lucia" => "LC",
            "Saint Martin" => "MF",
            "Saint Pierre and Miquelon" => "PM",
            "Samoa" => "WS",
            "San Marino" => "SM",
            "Saudi Arabia" => "SA",
            "Senegal" => "SN",
            "Serbia" => "RS",
            "Seychelles" => "SC",
            "Sierra Leone" => "SL",
            "Singapore" => "SG",
            "Slovakia" => "SK",
            "Slovenia" => "SI",
            "Solomon Islands" => "SB",
            "Somalia" => "SO",
            "South Africa" => "ZA",
            "South Korea" => "KR",
            "Spain" => "ES",
            "Sri Lanka" => "LK",
            "Sudan" => "SD",
            "Suriname" => "SR",
            "Svalbard and Jan Mayen" => "SJ",
            "Swaziland" => "SZ",
            "Sweden" => "SE",
            "Switzerland" => "CH",
            "Syria" => "SY",
            "Taiwan" => "TW",
            "Tajikistan" => "TJ",
            "Tanzania" => "TZ",
            "Thailand" => "TH",
            "Timor-Leste" => "TL",
            "Togo" => "TG",
            "Tokelau" => "TK",
            "Tonga" => "TO",
            "Trinidad and Tobago" => "TT",
            "Tunisia" => "TN",
            "Turkey" => "TR",
            "Turkmenistan" => "TM",
            "Turks and Caicos Islands" => "TC",
            "Tuvalu" => "TV",
            "Uganda" => "UG",
            "Ukraine" => "UA",
            "United Arab Emirates" => "AE",
            "United Kingdom" => "GB",
            "United States" => "US",
            "Uruguay" => "UY",
            "U.S. Outlying Islands" => "UM",
            "U.S. Virgin Islands" => "VI",
            "Uzbekistan" => "UZ",
            "Vanuatu" => "VU",
            "Vatican City" => "VA",
            "Venezuela" => "VE",
            "Vietnam" => "VN",
            "Wallis and Futuna" => "WF",
            "Western Sahara" => "EH",
            "Yemen" => "YE",
            "Zambia" => "ZM",
            "Zimbabwe" => "ZW"
        ];

        $countryId = null;
        if (isset($countries[$countryName])) {
            $countryId = $countries[$countryName];
        }
        return $countryId;
    }
    /**
     * Aux method for get custom mapping
     *
     * @return array
     */
    private function getCustomMapping()
    {
        $helper = $this->_getHelper();

        $keys = ["", "_2", "_3", "_4"];

        $customAttrKey = 'pitbulk_saml2_customer/custom_field_mapping/';
        $customMapping = [];

        foreach ($keys as $key) {
            $customAttrCode =  $helper->getConfig($customAttrKey . 'custom_attribute_code' . $key);
            $customAttrMapping =  $helper->getConfig($customAttrKey . 'custom_attribute_mapping' . $key);

            if (isset($customAttrCode) && isset($customAttrMapping)) {
                $customMapping[$customAttrCode] = $customAttrMapping;
            }
        }

        return $customMapping;
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Process Error
     *
     * @param string $errorMsg
     * @param string $extraInfo
     * @return void
     */
    public function processError($errorMsg, $extraInfo = null)
    {
        $this->logger->error($errorMsg);
        if (isset($extraInfo)) {
            $this->logger->error($extraInfo);
        }
        $storeId = $this->storeManager->getStore()->getId();
        $redirectTo =  $this->ssoHelper->getSsoRedirectionUrl($storeId);
        return $this->_redirect($redirectTo);
    }
}
