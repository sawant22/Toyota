<?php

namespace Infosys\Vehicle\Block\Adminhtml\Vehicle\Tab;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;

class ProductGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping\CollectionFactory
     */
    protected $vehicleProductMappingFactory;

    /**
     * Constructor function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
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
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Infosys\Vehicle\Model\ResourceModel\VehicleProductMapping\CollectionFactory $vehicleProductMappingFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Visibility $visibility = null,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
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
        if ($this->getRequest()->getParam('entity_id')) {
            $this->setDefaultFilter(['in_products' => 1]);
        } else {
            $this->setDefaultFilter(['in_products' => 0]);
        }
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
        $collection = $this->productFactory->create()->getCollection()->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'attribute_set_id'
        )->addAttributeToSelect(
            'type_id'
        )->setStore(
            $store
        );
        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }
        if ($store->getId()) {
            $collection->setStoreId($store->getId());
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                Store::DEFAULT_STORE_ID
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        $productIds = $this->_getSelectedProducts();
        $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
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
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
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
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type',
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
        return $this->getUrl('*/index/grid', ['_current' => true]);
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
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->vehicleProductMappingFactory->create()->addFieldToFilter('vehicle_id', $id);
        $grids = [];
        foreach ($model as $key => $value) {
            $grids[] = $value->getProductId();
        }
        $prodId = [];
        foreach ($grids as $obj) {
            $prodId[$obj] = ['position' => "0"];
        }
        return $prodId;
    }
}
