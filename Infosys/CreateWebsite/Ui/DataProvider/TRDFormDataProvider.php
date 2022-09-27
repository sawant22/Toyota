<?php
/**
 * @package Infosys/CreateWebsite
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CreateWebsite\Ui\DataProvider;

use Infosys\CreateWebsite\Model\ResourceModel\TRD\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class TRDFormDataProvider extends AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $trdCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $trdCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $trdCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $region) {
            $this->loadedData[$region->getId()] = $region->getData();
        }
        return $this->loadedData;
    }
}
