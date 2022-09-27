<?php

/**
 * @package     Infosys/DirectFulFillment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\DirectFulFillment\Block\Adminhtml\Order\View;

use \Magento\Framework\Serialize\Serializer\Json;
use Infosys\DirectFulFillment\Model\ResourceModel\FreightRecovery\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class to override the sales order view page content
 */
class View extends \Magento\Backend\Block\Template
{
    /**
     * @var Registry
     */
    private $_coreRegistry;

    /**
     * @var Json
     */
    protected $json;
    /**
     * @var CollectionFactory
     */
    protected $freightCollectionFactory;

    protected TimezoneInterface $timezone;

    protected ResolverInterface $localeResolver;

    /**
     * Constructor function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CollectionFactory $freightCollectionFactory
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        CollectionFactory $freightCollectionFactory,
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver,
        Json $json,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->json = $json;
        $this->freightCollectionFactory = $freightCollectionFactory;
        $this->timezone = $timezone;
        $this->localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Method to get ServiceFee
     *
     * @return array
     */
    public function getServiceFee()
    {
        return $this->getOrder()->getServiceFee();
    }
    /**
     * Method to getFreightRecovery
     *
     * @return array
     */
    public function getFreightRecovery()
    {
        $collection = $this->freightCollectionFactory->create();
        $collection->addFieldToFilter('order_id', ['eq' => $this->getOrder()->getId()]);
        return $collection;
    }

    /**
     * Function to format the date based on timezone
     *
     * @param string $curDate
     * @return void
     */
    public function getCreatedAtFormatted($curDate)
    {
        if (!empty($curDate)) {
            return date("M d, Y, h:i:s A", strtotime($this->timezone->formatDateTime(
                new \DateTime($curDate),
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::SHORT,
                $this->localeResolver->getDefaultLocale(),
                $this->timezone->getConfigTimezone('store', $this->getOrder()->getStore())
            )));
        }
    }
}
