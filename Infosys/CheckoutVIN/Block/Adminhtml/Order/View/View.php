<?php
/**
 * @package     Infosys/CheckoutVIN
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CheckoutVIN\Block\Adminhtml\Order\View;

use \Magento\Framework\Serialize\Serializer\Json;

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
     * Constructor function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        Json $json,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->json = $json;
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
     * Method to get vin details
     *
     * @return array
     */
    public function getVinData()
    {
        $vin_details = $this->getOrder()->getVinDetails();
        if ($vin_details) {
            return $this->getJsonDecode($vin_details);
        }
    }
     
     /**
      * Json data
      *
      * @param array $response
      * @return array
      */
    public function getJsonDecode($response)
    {
        return $this->json->unserialize($response); // it's same as like json_decode
    }
}
