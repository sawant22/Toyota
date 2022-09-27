<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright � 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Helper;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Infosys\DirectFulFillment\Model\WSSESoap;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use Infosys\DirectFulFillment\Logger\DDOALogger;
use ArrayAccess;
use Exception;
use UnexpectedValueException;
use DateTime;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Context;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PARAM_NAME_BASE64_URL = 'r64';
    const PARAM_NAME_URL_ENCODED = 'uenc';
    const CURL_STATUS = "200";
    const SSO_LOGIN_URL = "sso/general/login_url";
    const SSO_SERVICE_URL = "sso/general/service_url";
    const SSO_PORTAL_URL = "sso/general/portal_url";
    const EAUTH_PORTAL_LOGOUT_URL = "sso/general/eauth_logout_url";

    // Admin configuration for Payload and Access Token
    const SECURITY_KEY = "df_config/df_integration_settings/security_key";
    const SECURITY_CERTIFICATE = "df_config/df_integration_settings/securityCertificate";
    const CLIENT_ID = "df_config/df_accesstoken/client_id";
    const RESOURCE_URL = "df_config/df_accesstoken/resource";
    const JTI = "df_config/df_accesstoken/jti";

    const X5T = "df_config/df_accesstoken/x5t";
    const ACCESSTOKEN_URL = "df_config/df_accesstoken/accesstokenUrl";
    const GRANT_TYPE = "df_config/df_accesstoken/grant_type";
    const CLIENT_ASSERTION_TYPE = "df_config/df_accesstoken/client_assertion_type";
    const DDOA_API_URL = "df_config/df_integration_settings/ddao_api_url";
    const DDOA_LOG_ENABLED = 'df_config/ddoa_logging_group/ddoa_log';
    const DDOA_DEFAULT_TIMEOUT =  'df_config/ddoa_api_timeout/ddoa_connection_timeout';
    const DDOA_REQUEST_TIMEOUT =  'df_config/ddoa_api_timeout/ddoa_request_timeout';

    public static $leeway = 0;
    public static $timestamp = null;
    public static $supported_algs = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS512' => ['hash_hmac', 'SHA512'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'RS256' => ['openssl', 'SHA256'],
        'RS384' => ['openssl', 'SHA384'],
        'RS512' => ['openssl', 'SHA512'],
    ];
            
    protected ScopeConfigInterface $_scopeConfig;
            
    protected CustomerRepositoryInterface $customerRepo;
            
    protected CustomerFactory $customerFactory;
            
    protected StoreManagerInterface $storeManager;

    protected DDOALogger $logger;
            
    protected ManagerInterface $_messageManager;
            
    protected OrderRepositoryInterface $_orderRepository;
            
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;
    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Curl $curl
     * @param CustomerRepositoryInterface $customerRepo
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param RegionFactory $regionFactory
     * @param AddressInterfaceFactory $dataAddressFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param DDOALogger $logger
     * @param ManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        CustomerRepositoryInterface $customerRepo,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        RegionFactory $regionFactory,
        AddressInterfaceFactory $dataAddressFactory,
        AddressRepositoryInterface $addressRepository,
        ManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DirectoryList $directoryList,
        File $driverFile,
        DDOALogger $logger
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_curl = $curl;
        $this->customerRepo = $customerRepo;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->_regionFactory = $regionFactory;
        $this->dataAddressFactory = $dataAddressFactory;
        $this->addressRepository = $addressRepository;
        $this->_messageManager = $messageManager;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->logger = $logger;
    }

    /**
     * Decode IdToken
     * @param $token
     * @param $allowed_algs
     * @throws \Exception
     */
    public function decode($jwt, $keyOrKeyArray)
    {
        // Validate JWT
        $timestamp = \is_null(static::$timestamp) ? \time() : static::$timestamp;

        if (empty($keyOrKeyArray)) {
            throw new \InvalidArgumentException('Key may not be empty');
        }
        $tks = \explode('.', $jwt);
        if (\count($tks) != 3) {
            throw new \UnexpectedValueException('Wrong number of segments');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = static::jsonDecode(static::urlsafeB64Decode($headb64)))) {
            throw new \UnexpectedValueException('Invalid header encoding');
        }
        if (null === $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64))) {
            throw new \UnexpectedValueException('Invalid claims encoding');
        }
        if (false === ($sig = static::urlsafeB64Decode($cryptob64))) {
            throw new \UnexpectedValueException('Invalid signature encoding');
        }
        if (empty($header->alg)) {
            throw new \UnexpectedValueException('Empty algorithm');
        }
        if (empty(static::$supported_algs[$header->alg])) {
            throw new \UnexpectedValueException('Algorithm not supported');
        }

        $key = self::getKey($keyOrKeyArray, empty($header->kid) ? null : $header->kid);

        // Check the algorithm
        if (!self::constantTimeEquals($key->getAlgorithm(), $header->alg)) {
            // See issue #351
            throw new \UnexpectedValueException('Incorrect key for this algorithm');
        }
        if ($header->alg === 'ES256' || $header->alg === 'ES384') {
            // OpenSSL expects an ASN.1 DER sequence for ES256/ES384 signatures
            $sig = self::signatureToDER($sig);
        }
        if (!static::verify("$headb64.$bodyb64", $sig, $key->getKeyMaterial(), $header->alg)) {
            throw new UnexpectedValueException('Signature verification failed');
        }

        // Check the nbf if it is defined. This is the time that the
        // token can actually be used. If it's not yet that time, abort.
        if (isset($payload->nbf) && $payload->nbf > ($timestamp + static::$leeway)) {
            throw new UnexpectedValueException(
                'Cannot handle token prior to ' . \date(DateTime::ISO8601, $payload->nbf)
            );
        }

        // Check that this token has been created before 'now'. This prevents
        // using tokens that have been created for later use (and haven't
        // correctly used the nbf claim).
        if (isset($payload->iat) && $payload->iat > ($timestamp + static::$leeway)) {
            throw new UnexpectedValueException(
                'Cannot handle token prior to ' . \date(DateTime::ISO8601, $payload->iat)
            );
        }

        // Check if this token has expired.
        if (isset($payload->exp) && ($timestamp - static::$leeway) >= $payload->exp) {
            throw new UnexpectedValueException('Expired token');
        }

        return $payload;
    }
    /**
     * Determine if an algorithm has been provided for each Key
     *
     * @param Key|array<Key>|mixed $keyOrKeyArray
     * @param string|null $kid
     *
     * @throws UnexpectedValueException
     *
     * @return array containing the keyMaterial and algorithm
     */
    private static function getKey($keyOrKeyArray, $kid = null)
    {
        if ($keyOrKeyArray instanceof Key) {
            return $keyOrKeyArray;
        }

        if (is_array($keyOrKeyArray) || $keyOrKeyArray instanceof ArrayAccess) {
            foreach ($keyOrKeyArray as $keyId => $key) {
                if (!$key instanceof Key) {
                    throw new UnexpectedValueException(
                        '$keyOrKeyArray must be an instance of Firebase\JWT\Key key or an '
                            . 'array of Firebase\JWT\Key keys'
                    );
                }
            }
            if (!isset($kid)) {
                throw new UnexpectedValueException('"kid" empty, unable to lookup correct key');
            }
            if (!isset($keyOrKeyArray[$kid])) {
                throw new UnexpectedValueException('"kid" invalid, unable to lookup correct key');
            }

            return $keyOrKeyArray[$kid];
        }

        throw new UnexpectedValueException(
            '$keyOrKeyArray must be an instance of Firebase\JWT\Key key or an '
                . 'array of Firebase\JWT\Key keys'
        );
    }

    /** ----------------------------------------------------------------------------------------------------------
     * Converts and signs a PHP object or array into a JWT string.
     *
     * @param object|array  $payload    PHP object or array
     * @param string        $key        The secret key.
     *                                  If the algorithm used is asymmetric, this is the private key
     * @param string        $alg        The signing algorithm.
     *                                  Supported algorithms are 'HS256', 'HS384', 'HS512' and 'RS256'
     * @param mixed         $keyId
     * @param array         $head       An array with header elements to attach
     *
     * @return string A signed JWT
     *
     */
    public function encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null)
    {
        $x5t = $this->_scopeConfig->getValue(self::X5T);
        $header = ['typ' => 'JWT', 'alg' => $alg, 'x5t' => $x5t];

        if ($keyId !== null) {
            $header['kid'] = $keyId;
        }

        if (isset($head) && is_array($head)) {
            $header = array_merge($head, $header);
        }

        $segments = [];
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($header));
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($payload));
        $signing_input = implode('.', $segments);
        $signature = static::sign($signing_input, $key, $alg);
        $segments[] = static::urlsafeB64Encode($signature);

        return implode('.', $segments);
    }
    /** ----------------------------------------------------------------------------------------------------------
     * Sign a string with a given key and algorithm.
     *
     * @param string            $msg    The message to sign
     * @param string|resource   $key    The secret key
     * @param string            $alg    The signing algorithm.
     *                                  Supported algorithms are 'HS256', 'HS384', 'HS512' and 'RS256'
     *
     * @return string An encrypted message
     *
     */
    public static function sign($msg, $key, $alg = 'HS256')
    {
        if (empty(static::$supported_algs[$alg])) {
            throw new InputException('Algorithm not supported');
        }
        list($function, $algorithm) = static::$supported_algs[$alg];
        switch ($function) {
            case 'hash_hmac':
                return hash_hmac($algorithm, $msg, $key, true);
            case 'openssl':
                $signature = '';
                $success = openssl_sign($msg, $signature, $key, $algorithm);
                if (!$success) {
                    throw new InputException("OpenSSL unable to sign data");
                } else {
                    return $signature;
                }
        }
    }
    /** ----------------------------------------------------------------------------------------------------------
     * Verify a signature with the message, key and method. Not all methods
     * are symmetric, so we must have a separate verify and sign method.
     *
     * @param string            $msg        The original message (header and body)
     * @param string            $signature  The original signature
     * @param string|resource   $key        For HS*, a string key works. for RS*, must be a resource of an openssl public key
     * @param string            $alg        The algorithm
     *
     * @return bool
     */
    private static function verify($msg, $signature, $key, $alg)
    {
        if (empty(static::$supported_algs[$alg])) {
            throw new InputException('Algorithm not supported');
        }
        list($function, $algorithm) = static::$supported_algs[$alg];
        switch ($function) {
            case 'openssl':
                $success = openssl_verify($msg, $signature, $key, $algorithm);
                if ($success === 1) {
                    return true;
                } elseif ($success === 0) {
                    return false;
                }
                // returns 1 on success, 0 on failure, -1 on error.
                throw new InputException(
                    'OpenSSL error: ' . openssl_error_string()
                );
            case 'hash_hmac':
            default:
                $hash = hash_hmac($algorithm, $msg, $key, true);
                if (function_exists('hash_equals')) {
                    return hash_equals($signature, $hash);
                }
                $len = min(static::safeStrlen($signature), static::safeStrlen($hash));
                $status = 0;
                for ($i = 0; $i < $len; $i++) {
                    $status |= (ord($signature[$i]) ^ ord($hash[$i]));
                }
                $status |= (static::safeStrlen($signature) ^ static::safeStrlen($hash));
                return ($status === 0);
        }
    }
    /** ----------------------------------------------------------------------------------------------------------
     * Decode a JSON string into a PHP object.
     *
     * @param string $input JSON string
     *
     * @return object Object representation of JSON string
     *
     * @throws InputException Provided string was invalid JSON
     */
    public static function jsonDecode($input)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            /** In PHP >=5.4.0, json_decode() accepts an options parameter, that allows you
             * to specify that large ints (like Steam Transaction IDs) should be treated as
             * strings, rather than the PHP default behaviour of converting them to floats.
             */
            $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            /** Not all servers will support that, however, so for older versions we must
             * manually detect large ints in the JSON string and quote them (thus converting
             *them to strings) before decoding, hence the preg_replace() call.
             */
            $max_int_length = strlen((string) PHP_INT_MAX) - 1;
            $json_without_bigints = preg_replace('/:\s*(-?\d{' . $max_int_length . ',})/', ': "$1"', $input);
            $obj = json_decode($json_without_bigints);
        }
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($obj === null && $input !== 'null') {
            throw new InputException('Null result with non-null input');
        }
        return $obj;
    }
    /** ----------------------------------------------------------------------------------------------------------
     * Encode a PHP object into a JSON string.
     *
     * @param object|array $input A PHP object or array
     *
     * @return string JSON representation of the PHP object or array
     *
     * @throws InputException Provided object could not be encoded to valid JSON
     */
    public static function jsonEncode($input)
    {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($json === 'null' && $input !== null) {
            throw new InputException('Null result with non-null input');
        }
        return $json;
    }
    /** ----------------------------------------------------------------------------------------------------------
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string A decoded string
     */
    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
    /** ----------------------------------------------------------------------------------------------------------
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string The base64 encode of what you passed in
     */
    public static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
    /** ----------------------------------------------------------------------------------------------------------
     * Helper method to create a JSON error.
     *
     * @param int $errno An error number from json_last_error()
     *
     * @return void
     */
    private static function handleJsonError($errno)
    {
        $messages = [
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters' //PHP >= 5.3.3
        ];
        throw new InputException(__(
            isset($messages[$errno])
                ? $messages[$errno]
                : 'Unknown JSON error: ' . $errno)
        );
    }
    /** ----------------------------------------------------------------------------------------------------------
     * Get the number of bytes in cryptographic strings.
     *
     * @param string
     *
     * @return int
     */
    private static function safeStrlen($str)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, '8bit');
        }
        return strlen($str);
    }
    /**
     * Get Access Token
     *
     */
    public function getAccessToken()
    {
        $date = date('Ymd his');
        $date = date('Y-m-d', strtotime($date . ' +1 day'));

        // Admin configuration values
        $privateKey = $this->_scopeConfig->getValue(self::SECURITY_KEY);
        $clientId = $this->_scopeConfig->getValue(self::CLIENT_ID);
        $resource = $this->_scopeConfig->getValue(self::RESOURCE_URL);
        $accesstokenUrl = $this->_scopeConfig->getValue(self::ACCESSTOKEN_URL);
        $jti = $this->_scopeConfig->getValue(self::JTI);
        $grand_type = $this->_scopeConfig->getValue(self::GRANT_TYPE);
        $client_assertion_type = $this->_scopeConfig->getValue(self::CLIENT_ASSERTION_TYPE);
        $connectionTimeout = $this->_scopeConfig->getValue(self::DDOA_DEFAULT_TIMEOUT);
        $requestTimeout = $this->_scopeConfig->getValue(self::DDOA_REQUEST_TIMEOUT);

        $payload = [
            "iss" => $clientId,
            "aud" => $accesstokenUrl,
            "exp" => strtotime($date) + (60 * 60 * 5),
            "sub" => $clientId,
            "nbf" => strtotime($date) + (60 * 60 * 5),
            "jti" => $jti
        ];

        $jwt = $this->encode($payload, $privateKey, 'RS256');
        $clientAccersionToken = $jwt;
        $accesstokenPostData = [
            'grant_type' => $grand_type,
            'client_assertion_type' => $client_assertion_type,
            'client_id' => $clientId,
            'resource' => $resource,
            'client_assertion' => $clientAccersionToken
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $accesstokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $requestTimeout,
            CURLOPT_CONNECTTIMEOUT => $connectionTimeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $accesstokenPostData,
        ]);
        $responseJson = curl_exec($curl);
        $curlErrNo = curl_errno($curl);
        $httpResponseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $response = json_decode($responseJson, true);
        if ($curlErrNo || $httpResponseCode != 200) {
            $this->logRequest($accesstokenUrl, $responseJson);
        } else {
            if (array_key_exists('access_token', $response)) {
                return $response['access_token'];
            } else {
                $errorMessage = "Error Creating Access Token: " . $responseJson;
                $this->logger->error($errorMessage);
                throw new LocalizedException(__($errorMessage));
            }
        }
        curl_close($curl);
    }
    /**
     * Generate WSL XML with certificate
     *
     * @param string $request
     * @return string
     */
    public function getSoapXmlRequest($request)
    {
        // Admin configuration values
        $privateKey = $this->_scopeConfig->getValue(self::SECURITY_KEY);
        $certificte = $this->_scopeConfig->getValue(self::SECURITY_CERTIFICATE);

        $doc = new \DOMDocument('1.0');
        $doc->loadXML($request);
        $objWSSE = new WSSESoap($doc);
        $objWSSE->addTimestamp();

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);

        $objKey->loadKey($privateKey, false);
        $options = ['insertBefore' => true];
        $objWSSE->signSoapDoc($objKey, $options);

        $token = $objWSSE->addBinaryToken($certificte);
        $objWSSE->attachTokentoSig($token);

        $options = ['issuerSerial' => true, 'subjectName' => true];
        $objXMLSecDSig = new XMLSecurityDSig(false);
        $add509Cert = $objXMLSecDSig->add509Cert($certificte, $isPEMFormat = true, $isURL = false, $options);


        $security = $objWSSE->locateSecurityHeader();
        foreach ($security->childNodes as $node) {
            $nodee = $node->parentNode;
            $objXMLSecDSig->sign($objKey, $nodee);
            break;
        };
        $document = $doc->saveXML();
        return $document;
    }
    /**
     * Get DDOA Api Url
     *
     * @return string
     */
    public function getDDOAUrl()
    {
        return $this->_scopeConfig->getValue(self::DDOA_API_URL);
    }

    /**
     * Get DDOA Log Enable/Disable
     *
     * @return string
     */
    public function isLogEnabled()
    {
        return $this->_scopeConfig->getValue(self::DDOA_LOG_ENABLED);
    }

    /**
     * Add DDOA curl request log
     *
     * @param string $url
     * @param string $token
     * @param string $soapXml
     * @return void
     */
    public function logRequest($url, $responseJson)
    {
        $request = "curl --location --request POST '" . $url . "'response " . $responseJson;
        $this->logger->error('DDOA Curl Token Request: ' . json_encode($request));
    }
}