<?php

namespace Infosys\Vehicle\Block\Adminhtml\Product\Tab;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;

class VehicleGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $vehicleFactory;
    /**
     * @var \Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping\CollectionFactory
     */
    protected $vehicleProductMappingFactory;

    /**
     * Constructor function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Infosys\Vehicle\Model\VehicleFactory $vehicleFactory
     * @param \Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping\CollectionFactory $vehicleProductMappingFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Visibility|null $visibility
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Infosys\Vehicle\Model\VehicleFactory $vehicleFactory,
        \Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping\CollectionFactory $vehicleProductMappingFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Visibility $visibility = null,
        array $data = []
    ) {
        $this->vehicleFactory = $vehicleFactory;
        $this->vehicleProductMappingFactory = $vehicleProductMappingFactory;
        $this->coreRegistry = $coreRegistry;
        $this->moduleManager = $moduleManager;
        $this->_storeManager = $storeManager;
        $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rh_grid_products');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Get Store
     *
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Perpare Vehicle Product Mapping Collection
     *
     * @return void
     */
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = $this->vehicleFactory->create()->getCollection()->addFieldToSelect('*');
        $vehicleIds = $this->_getSelectedProducts();
        $collection->addFieldToFilter('entity_id', ['in' => $vehicleIds]);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Column filter
     *
     * @param object $column
     * @return void
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $vehicleIds = $this->_getSelectedProducts();
            if (empty($vehicleIds)) {
                $vehicleIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $vehicleIds]);
            } else {
                if ($vehicleIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $vehicleIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Perpare Column
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'width' => '50px',
                'index' => 'entity_id',
                'type' => 'number',
            ]
        );
        $this->addColumn(
            'brand',
            [
                'header' => __('Brand'),
                'index' => 'brand',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
            ]
        );
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
            ]
        );
        $this->addColumn(
            'model_year',
            [
                'header' => __('Model Year'),
                'index' => 'model_year',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $this->addColumn(
            'model_code',
            [
                'header' => __('Model Code'),
                'index' => 'model_code',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $this->addColumn(
            'series_name',
            [
                'header' => __('Series Name'),
                'index' => 'series_name',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $this->addColumn(
            'grade',
            [
                'header' => __('Grade'),
                'index' => 'grade',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $this->addColumn(
            'driveline',
            [
                'header' => __('Driveline'),
                'index' => 'driveline',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $this->addColumn(
            'view',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => 'vehicle/index/view',
                            'params' => ['entity_id']
                        ],
                        'field' => 'entity_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'entity_id',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('vehicle/index/vehicleGrid', ['_current' => true]);
    }

    /**
     * Get Selected Products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = array_keys($this->getSelectedProducts());
        return $products;
    }

    /**
     * Get Selected Products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->vehicleProductMappingFactory->create()->addFieldToFilter('product_id', $id);
        $grids = [];
        foreach ($model as $key => $value) {
            $grids[] = $value->getVehicleId();
        }
        $prodId = [];
        foreach ($grids as $obj) {
            $prodId[$obj] = ['position' => "0"];
        }
        return $prodId;
    }
}
