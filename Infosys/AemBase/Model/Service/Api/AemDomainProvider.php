<?php
/**
 * @package     Infosys/AemBase
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\AemBase\Model\Service\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AppTokenProvider
 */
class AemDomainProvider
{
    /**
     * Aem Domain config path
     */
    const AEM_DOMAIN = 'aem_general_config/general/aem_domain';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * AppTokenProvider constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->scopeConfig->getValue(self::AEM_DOMAIN);
    }
}
