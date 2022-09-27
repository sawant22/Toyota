<?php

/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\VehicleSearch\Model\Indexer\Fulltext\Action;

use Magento\Framework\Filter\RemoveTags;
use Infosys\VehicleSearch\Model\ResourceModel\Vehicle\Indexer\Fulltext as ResourceModel;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;

/**
 * ElasticSearch CMS Pages full indexer
 *
 */
class Full
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    private $areaList;

    /**
     * @var \Magento\Framework\Filter\RemoveTags
     */
    private $stripTags;

    /**
     * Constructor function
     *
     * @param ResourceModel $resourceModel
     * @param FilterProvider $filterProvider
     * @param AreaList $areaList
     * @param RemoveTags $stripTags
     */
    public function __construct(
        ResourceModel $resourceModel,
        FilterProvider $filterProvider,
        AreaList $areaList,
        RemoveTags $stripTags
    ) {
        $this->resourceModel  = $resourceModel;
        $this->filterProvider = $filterProvider;
        $this->areaList       = $areaList;
        $this->stripTags      = $stripTags;
    }

    /**
     * Rebuild whole fulltext index for all stores
     *
     * @deprecated 100.1.6 Please use \Magento\CatalogSearch\Model\Indexer\Fulltext::executeFull instead
     * @see \Magento\CatalogSearch\Model\Indexer\Fulltext::executeFull
     * @return void
     */
    public function reindexAll()
    {
        $storeId = 0;
        $this->cleanIndex($storeId);
        $this->rebuildStoreIndex($storeId);
        $this->searchRequestConfig->reset();
    }

    /**
     * Regenerate vehicle entitty
     *
     * @param array|null $entityIds Vehicle Entity Id
     * @return \Traversable
     */
    public function rebuildIndex($entityIds = null)
    {
        $lastVehicleEnityId = 0;
        do {
            $vehicles = $this->getSearchableVehicles($entityIds, $lastVehicleEnityId);
            if (count($vehicles) > 0) {

                foreach ($vehicles as $vehicle) {
                    $vehicleData = $this->processVehicleData($vehicle);
                    $lastVehicleEnityId = (int) $vehicleData['entity_id'];
                    yield $lastVehicleEnityId => $vehicleData;
                }
            }
        } while (!empty($vehicles));
    }

    /**
     * Load a bulk of vehicle data.
     *
     * @param string  $entityIds
     * @param integer $fromId
     * @param integer $limit
     *
     * @return array
     */
    private function getSearchableVehicles($entityIds = null, $fromId = 0, $limit = 100)
    {
        return $this->resourceModel->getSearchableVehicle($entityIds, $fromId, $limit);
    }

    /**
     * Parse template processor page content
     *
     * @param array $vehicleData  page data.
     *
     * @return array
     */
    private function processVehicleData($vehicleData)
    {
        unset($vehicleData['model_range']);
        return $vehicleData;
    }
}
