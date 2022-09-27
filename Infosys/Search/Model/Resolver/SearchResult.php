<?php
/**
 * @package   Infosys/Search
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\Search\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Search\Model\QueryFactory;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Repository\IndexRepository;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

class SearchResult implements ResolverInterface
{
    /**
     * @var boolean
     */
    private static $isLayerCreated = false;
    /**
     * @var IndexRepository
     */
    private $indexRepository;
    /**
     *
     * @var QueryFactory
     */
    private $queryFactory;
    /**
     *
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * Constructor function
     *
     * @param IndexRepository $indexRepository
     * @param QueryFactory $queryFactory
     * @param LayerResolver $layerResolver
     */
    public function __construct(
        IndexRepository $indexRepository,
        QueryFactory $queryFactory,
        LayerResolver $layerResolver
    ) {
        $this->indexRepository = $indexRepository;
        $this->queryFactory    = $queryFactory;
        $this->layerResolver   = $layerResolver;
    }
    /**
     * Resolver function
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (!isset($args['query'])) {
            throw new GraphQlInputException(__('"Query should be specified'));
        }
        if (!self::$isLayerCreated) {
            try {
                $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
            } catch (\Exception $e) {
            } finally {
                self::$isLayerCreated = true;
            }
        }
        $query = $this->queryFactory->get()
            ->setQueryText($args['query'])
            ->setData('is_query_text_short', false);
        $suggestionData = [];
        foreach ($query->getSuggestCollection() as $resultItem) {
            $suggestionData[] = $resultItem->getQueryText();
        }
        $collection = $this->indexRepository->getCollection()
            ->addFieldToFilter(IndexInterface::IS_ACTIVE, 1)
            ->setOrder(IndexInterface::POSITION, 'asc');
        $indexList = [];
        foreach ($collection as $index) {
            $indexItem = [
                IndexInterface::IDENTIFIER => $index->getIdentifier(),
                IndexInterface::TITLE      => $index->getTitle(),
                IndexInterface::POSITION   => $index->getPosition(),
            ];

            $indexInstance = $this->indexRepository->getInstance($index);

            $itemsCollection = $indexInstance->getSearchCollection();
            if ($itemsCollection->getSize()) {
                $query->saveNumResults($itemsCollection->getSize());
                $query->saveIncrementalPopularity();
            }
            $indexItem['collection'] = $itemsCollection;
            $indexItem['size'] = $itemsCollection->getSize();

            $indexList[$index->getIdentifier()] = $indexItem;
        }
        $indexList['search_terms'] = $suggestionData;

        return $indexList;
    }
}
