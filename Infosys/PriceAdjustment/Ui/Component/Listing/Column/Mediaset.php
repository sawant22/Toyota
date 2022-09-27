<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Class Store
 */
class Mediaset extends Column
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $storeKey;

    /**
     * Store manager
     *
     * @var WebsiteRepositoryInterface
     */
    protected $website;
    /**
     * @var AttributeOptionInterfaceFactory
     */
    protected $optionFactory;
    /**
     * @var Collection
     */
    protected $_attributeOptionCollection;
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     * @param WebsiteRepositoryInterface $website
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory
     * @param array $components
     * @param array $data
     * @param string $storeKey
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        Escaper $escaper,
        WebsiteRepositoryInterface $website,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        array $components = [],
        array $data = [],
        $storeKey = 'store_id'
    ) {
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
        $this->storeKey = $storeKey;
        $this->website = $website;
        $this->optionFactory = $optionFactory;
        $this->_attributeOptionCollection = $attributeOptionCollection;
        $this->productFactory = $productFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['media_set_selector'] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        if (empty($item['media_set_selector'])) {
            return '';
        }
        $poductReource=$this->productFactory->create();
        $attribute = $poductReource->getAttribute('tier_price_set');
        if ($attribute->usesSource()) {
            return $option_Text = $attribute->getSource()->getOptionText($item['media_set_selector']);
        }
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->getStoreManager()->isSingleStoreMode()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }

    /**
     * Get StoreManager dependency
     *
     * @return StoreManager
     *
     * @deprecated 100.1.0
     */
    private function getStoreManager()
    {
        if ($this->storeManager === null) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->storeManager;
    }
}
