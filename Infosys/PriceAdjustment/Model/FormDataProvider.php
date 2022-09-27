<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model;

use Infosys\PriceAdjustment\Model\ResourceModel\Media\CollectionFactory;

use Infosys\PriceAdjustment\Model\TierFactory;

class FormDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;
    /**
     * @var CollectionFactory
     */
    protected $collection;
    
    /**
     * @var CollectionFactory
     */
    protected $tierFactory;
    
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $mediaCollectionFactory
     * @param TierFactory $tierFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $mediaCollectionFactory,
        TierFactory $tierFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $mediaCollectionFactory->create();
        $this->tierFactory = $tierFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    
    /**
     * Get all options
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getFirstItem();
        $itemsData = $items->getData();
        $tierData = $this->getTierPricesByMediaSet($items->getData('entity_id'));
        foreach ($tierData as $tier) {
            $itemsData['mediaset_percentage_form_container'][]=  $tier->getData();
        }
        $this->loadedData[$items->getData('entity_id')] =  $itemsData;
        return $this->loadedData;
    }

    /**
     * Get Tier Price Dynamic Rows by entity id
     *
     * @param int $id
     *
     * @return array
     */
    public function getTierPricesByMediaSet($id)
    {
        return $this->tierFactory->create()->getCollection()->addFieldToFilter('entity_id', $id);
    }
}
