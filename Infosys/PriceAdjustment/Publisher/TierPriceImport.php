<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\PriceAdjustment\Publisher;

use Magento\Framework\MessageQueue\PublisherInterface;
use Infosys\PriceAdjustment\Model\TierQueueFactory;

/**
 * Class TierPriceImport
 */
class TierPriceImport
{

    public const TOPIC_NAME = "magento.tier-price.import";

    /**
     * @var PublisherInterface
     */
    private PublisherInterface $publisher;

    /**
     * Price import constructor
     *
     * @param PublisherInterface $publisher
     */
    public function __construct(
        PublisherInterface $publisher
    ) {
        $this->publisher = $publisher;
    }

    /**
     *
     * @param mixed $data
     * @return bool
     */
    public function publish($data): bool
    {
        $this->publisher->publish(self::TOPIC_NAME, $data);
        return true;
    }
}
