<?php

/**
 * @package   Infosys/ProductSaleable
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\ProductSaleable\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class ProductHandler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    public $fileName = '/var/log/toyota_product_threshold_price.log';
}