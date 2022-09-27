<?php
/**
 * @package   Infosys/CustomOrderNumber
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\CustomOrderNumber\Model;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\SalesSequence\Model\Meta;
use Infosys\CustomOrderNumber\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Backend\Model\Session\Quote as AdminQuoteSession;
use \Magento\Framework\App\State as AppState;

/**
 * Custom order number class
 */
class CustomSequence extends \Magento\SalesSequence\Model\Sequence
{
    /**
     * @var string
     */
    protected $lastIncrementId;

    protected Meta $meta;

    /**
     * @var false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $pattern;

    protected Data $helper;
    
    protected CheckoutSession $checkoutSession;

    protected AdminQuoteSession $backendQuoteSession;

    protected AppState $state;
    /**
     * Constructor function
     *
     * @param Meta $meta
     * @param AppResource $resource
     * @param Data $helper
     * @param CheckoutSession $checkoutSession
     * @param AdminQuoteSession $backendQuoteSession
     * @param AppState $state
     * @param string $pattern
     */
    public function __construct(
        Meta $meta,
        AppResource $resource,
        Data $helper,
        CheckoutSession $checkoutSession,
        AdminQuoteSession $backendQuoteSession,
        AppState $state,
        $pattern = \Magento\SalesSequence\Model\Sequence::DEFAULT_PATTERN
    ) {
        parent::__construct($meta, $resource, $pattern);

        $this->meta = $meta;
        $this->connection = $resource->getConnection('sales');
        $this->pattern = $pattern;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->state = $state;
    }

    /**
     * Retrieve current value
     *
     * @param callable $result
     * @return string
     */
    public function getCurrentValue(): string
    {
        if (!$this->helper->isEnabled()) {
            return parent::getCurrentValue();
        }

        if (!isset($this->lastIncrementId)) {
            $this->lastIncrementId = $this->connection->lastInsertId($this->meta->getSequenceTable());
        }
        $profile = $this->meta->getActiveProfile();
        if ($profile) {
            $prefix = "E" . $profile->getPrefix();
            $suffix = $profile->getSuffix();
        } else {
            $prefix = 'E';
            $suffix = '';
        }

        $entityType = $this->meta->getEntityType();
        if ($entityType == 'order') {
            if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
                $quoteSession = $this->backendQuoteSession;
            } else {
                $quoteSession = $this->checkoutSession;
            }
            $prefix = "E" . $quoteSession->getQuote()->getStore()->getWebsite()->getDealerCode();
        }
        $pattern        = $this->getPattern($this->meta->getEntityType());
        $currentValue   = $this->_calculateCurrentValue();
        $customOrderNumber = sprintf($pattern, $prefix, $currentValue, $suffix);
        return $customOrderNumber;
    }
    /**
     * get order number pattern
     *
     * @return string
     */
    public function getPattern(): string
    {
        if (!$this->helper->isEnabled()) {
            return $this->pattern;
        }

        $padding = 9; // default Magento value        

        $this->pattern = "%s%'.0{$padding}d%s";

        return $this->pattern;
    }

    /**
     * Calculate current value depends on start value
     *
     * @return int
     */
    protected function _calculateCurrentValue(): int
    {
        return ($this->lastIncrementId - $this->meta->getActiveProfile()->getStartValue())
            * $this->meta->getActiveProfile()->getStep() + $this->meta->getActiveProfile()->getStartValue();
    }
}
