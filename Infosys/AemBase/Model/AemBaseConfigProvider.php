<?php
/**
 * @package     Infosys/AemBase
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\AemBase\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class AemBaseConfigProvider
{
    /**
     * Enable Debug config path
     */
    const AEM_ENABLE_DEBUG = 'aem_general_config/dev/enable_debug_logging';
    const MAGENTO_PATH = 'aem_general_config/general/magento_path';
    const AEM_DOMAIN = 'aem_general_config/general/aem_domain';
    const AEM_PATH = 'aem_general_config/general/aem_path';
    const AEM_PRODUCT_PATH = 'aem_general_config/general/aem_product_path';
    const AEM_CATEGORY_PATH = 'aem_general_config/general/aem_category_path';
    const AEM_HOMEPAGE_PATH = 'aem_general_config/general/aem_homepage_path';
    const AEM_SITEMAP_PATH = 'aem_general_config/general/aem_sitemap_path';
    const AEM_AUTHOR_DOMAIN = 'aem_general_config/general/aem_author_domain';
    const AEM_AUTHOR_USERNAME = 'aem_general_config/author_authentication/username';
    const AEM_AUTHOR_PASSWORD = 'aem_general_config/author_authentication/password';

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var EncryptorInterface */
    protected $encryptor;

    /**
     * AemBaseConfigProvider constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface  $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @return bool is debug enabled for use on local only
     */
    public function isDebugEnabled() : bool
    {
        return (bool) $this->scopeConfig->getValue(self::AEM_ENABLE_DEBUG);
    }

    /**
     * @return string
     */
    public function getMagentoPath() : string
    {
        return $this->scopeConfig->getValue(self::MAGENTO_PATH);
    }

    /**
     * @return string
     */
    public function getAemDomain() : string
    {
        return $this->scopeConfig->getValue(self::AEM_DOMAIN);
    }

    /**
     * @return string
     */
    public function getAemAuthorDomain() : string
    {
        return $this->scopeConfig->getValue(self::AEM_AUTHOR_DOMAIN);
    }

    /**
     * @return string
     */
    public function getAemPath() : string
    {
        return $this->scopeConfig->getValue(self::AEM_PATH);
    }

    /**
     * @return string
     */
    public function getAemProductPath() : string
    {
        return $this->scopeConfig->getValue(self::AEM_PRODUCT_PATH);
    }

    /**
     * @return string
     */
    public function getAemCategoryPath() : string
    {
        return $this->getAemPath() . $this->scopeConfig->getValue(self::AEM_CATEGORY_PATH);
    }

    /**
     * @return string
     */
    public function getAemHomepagePath() : string
    {
        return $this->getAemPath() . $this->scopeConfig->getValue(self::AEM_HOMEPAGE_PATH);
    }

    /**
     * @return string
     */
    public function getAemSitemapUrl() : string
    {
        return $this->getAemDomain() . $this->scopeConfig->getValue(self::AEM_SITEMAP_PATH);
    }

    /**
     * @return string
     */
    public function getAemAuthorUserName() : string
    {
        return $this->scopeConfig->getValue(self::AEM_AUTHOR_USERNAME);
    }

    /**
     * @return string
     */
    public function getAemAuthorPassword() : string
    {
        $password = $this->scopeConfig->getValue(self::AEM_AUTHOR_PASSWORD);
        return $this->encryptor->decrypt($password);
    }
}
