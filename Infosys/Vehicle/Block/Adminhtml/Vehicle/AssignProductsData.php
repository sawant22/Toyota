<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Block\Adminhtml\Vehicle;

class AssignProductsData extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'products/assign_products_data.phtml';
    /**
     * Constructor function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Infosys\Vehicle\Model\ResourceModel\Vehicle\CollectionFactory $CollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Infosys\Vehicle\Model\ResourceModel\Vehicle\CollectionFactory $CollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->CollectionFactory = $CollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Vehicle Data
     *
     * @return void
     */
    public function getvehicleCollection()
    {
        $entity_id = $this->getRequest()->getParam('entity_id');
        $vehicleCollection = $this->CollectionFactory->create();
        $vehicleCollection->addFieldToFilter('entity_id', ['eq' => $entity_id]);
        return $vehicleCollection;
    }
}
