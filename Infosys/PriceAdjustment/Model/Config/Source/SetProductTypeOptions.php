<?php
/**
 * @package     Infosys/PriceAdjustment
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\PriceAdjustment\Model\Config\Source;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Exception;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;

class SetProductTypeOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $ProductAttributeRepositoryInterface;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductAttributeRepositoryInterface $ProductAttributeRepositoryInterface
     */
    public function __construct(
        ProductAttributeRepositoryInterface $ProductAttributeRepositoryInterface,
        AttributeSetRepositoryInterface $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->ProductAttributeRepositoryInterface = $ProductAttributeRepositoryInterface;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $attributeSetList = $this->listAttributeSet();
        if ($attributeSetList) {
            foreach ($attributeSetList->getItems() as $list) {
                if($list->getAttributeSetName() == 'Default') {
                    continue;
                }
                $this->_options[] = ['label' => $list->getAttributeSetName(), 'value' => $list->getAttributeSetId()];
            }
        }
        return $this->_options;
    }
    /**
     * list attribute set
     *
     * @return AttributeSetInterface|null
     */
    public function listAttributeSet()
    {
        $attributeSetList = null;
        try {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $attributeSet = $this->attributeSetRepository->getList($searchCriteria);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        if ($attributeSet->getTotalCount()) {
            $attributeSetList = $attributeSet;
        }

        return $attributeSetList;
    }
}
