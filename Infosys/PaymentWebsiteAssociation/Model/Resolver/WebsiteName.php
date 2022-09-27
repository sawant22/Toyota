<?php

/**
 * @package     Infosys/PaymentWebsiteAssociation
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\PaymentWebsiteAssociation\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\WebsiteRepositoryInterface;

class WebsiteName implements ResolverInterface
{
    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;
    /**
     * Constructor function
     *
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(WebsiteRepositoryInterface $websiteRepository)
    {
        $this->websiteRepository = $websiteRepository;
    }
    /**
     * Get saved card website name
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return void
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (isset($value['website_id']) && $value['website_id']) {
            return  $this->websiteRepository->getById($value['website_id'])->getName();
        }
    }
}
