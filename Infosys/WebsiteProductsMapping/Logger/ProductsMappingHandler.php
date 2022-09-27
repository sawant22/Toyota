<?php

/**
 * @package   Infosys/WebsiteProductsMapping
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\WebsiteProductsMapping\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Handler class for website products mapping logs
 */
class ProductsMappingHandler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    public $fileName = '/var/log/toyota_website_products_mapping.log';
}
