<?php

/**
 * @package Infosys/SearchTerm
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SearchTerm\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Search\Model\QueryFactory;

/**
 * Class to get search suggestions based on query
 */
class SearchTerms implements ResolverInterface
{
    protected QueryFactory $queryFactory;

    /**
     * Constructor function
     *
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        QueryFactory $queryFactory
    ) {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Resolver to get search terms based on query
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['query']) || empty($args['query'])) {
            throw new GraphQlInputException(__('Please enter valid Search Query'));
        }
        $query = $this->queryFactory->get()
            ->setQueryText($args['query'])
            ->setData('is_query_text_short', false);
        $suggestionData = [];
        foreach ($query->getSuggestCollection() as $resultItem) {
            $suggestionData[] = $resultItem->getQueryText();
        }
        return ['search_terms' => $suggestionData];
    }
}
