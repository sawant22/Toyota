<?php

/**
 * @package     Infosys/CustomerWebsiteOrders
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CustomerWebsiteOrders\Plugin;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Api\Search\FilterGroup;

/**
 * Class to get customer all website orders
 */
class WebsiteOrders
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Translator field from graphql to collection field
     *
     * @var string[]
     */
    private $fieldTranslatorArray = [
        'number' => 'increment_id',
    ];

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param string[] $fieldTranslatorArray
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        array $fieldTranslatorArray = []
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->fieldTranslatorArray = array_replace($this->fieldTranslatorArray, $fieldTranslatorArray);
    }

    /**
     * Overriding the method to get customer all website orders.
     *
     * @param OrderFilter $subject
     * @param Closure $proceed
     * @param array $args
     * @param int $userId
     * @param int $storeId
     * @return FilterGroup[]
     */
    public function aroundCreateFilterGroups(
        \Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query\OrderFilter $subject,
        \Closure $proceed,
        array $args,
        int $userId,
        int $storeId
    ): array {
        $filterGroups = [];
        $this->filterGroupBuilder->setFilters(
            [$this->filterBuilder->setField('customer_id')->setValue($userId)->setConditionType('eq')->create()]
        );
        $filterGroups[] = $this->filterGroupBuilder->create();

        if (isset($args['filter'])) {
            $filters = [];
            foreach ($args['filter'] as $field => $cond) {
                if (isset($this->fieldTranslatorArray[$field])) {
                    $field = $this->fieldTranslatorArray[$field];
                }
                foreach ($cond as $condType => $value) {
                    if ($condType === 'match') {
                        if (is_array($value)) {
                            throw new InputException(__('Invalid match filter'));
                        }
                        $searchValue = str_replace('%', '', $value);
                        $filters[] = $this->filterBuilder->setField($field)
                            ->setValue("%{$searchValue}%")
                            ->setConditionType('like')
                            ->create();
                    } else {
                        $filters[] = $this->filterBuilder->setField($field)
                            ->setValue($value)
                            ->setConditionType($condType)
                            ->create();
                    }
                }
            }

            $this->filterGroupBuilder->setFilters($filters);
            $filterGroups[] = $this->filterGroupBuilder->create();
        }
        return $filterGroups;
    }
}
