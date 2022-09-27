<?php

/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Block\Adminhtml\Vehicle;

use Infosys\Vehicle\Block\Adminhtml\Vehicle\Tab\ProductGrid;

class AssignProducts extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'products/assign_products.phtml';
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
     */
    protected $blockGrid;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var \Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping\CollectionFactory
     */
    protected $vehicleProductMappingFactory;
    /**
     * Constructor function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping\CollectionFactory $vehicleProductMappingFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping\CollectionFactory $vehicleProductMappingFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->vehicleProductMappingFactory = $vehicleProductMappingFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                ProductGrid::class,
                'vehicle.product.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * Get Product Json
     *
     * @return string
     */
    public function getProductsJson()
    {
        $entity_id = $this->getRequest()->getParam('entity_id');
        $vehicleproductMappingFactory = $this->vehicleProductMappingFactory->create();
        $vehicleproductMappingFactory->addFieldToSelect(['product_id']);
        $vehicleproductMappingFactory->addFieldToFilter('vehicle_id', ['eq' => $entity_id]);
        $result = [];
        if (!empty($vehicleproductMappingFactory->getData())) {
            foreach ($vehicleproductMappingFactory->getData() as $vehicleProducts) {
                $result[$vehicleProducts['product_id']] = '';
            }
            return $this->jsonEncoder->encode($result);
        }
        return '{}';
    }

    /**
     * Get Item
     *
     * @return void
     */
    public function getItem()
    {
        return $this->registry->registry('my_item');
    }
}
