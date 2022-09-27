<?php
/**
 * @package     Infosys/AemBase
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\AemBase\Model\Logger;

use Infosys\AemBase\Model\AemBaseConfigProvider;
use Psr\Log\LoggerInterface;

class Logger
{
    /**
     * @var AemBaseConfigProvider
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        AemBaseConfigProvider $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param $key
     * @param array $params
     * @return $this
     */
    public function debug($key, $params = []): self
    {
        if ($this->config->isDebugEnabled()) {
            $this->logger->debug($key, $params);
        }
        return $this;
    }

    /**
     * @param string $key
     * @param array $params
     *
     * @return $this
     */
    public function error($key, $params = []): self
    {
        $this->logger->error($key, $params);
        return $this;
    }
}
