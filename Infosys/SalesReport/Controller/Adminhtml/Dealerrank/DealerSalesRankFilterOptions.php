<?php

/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\SalesReport\Controller\Adminhtml\Dealerrank;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Infosys\SalesReport\Model\DealerSalesRankFilterOptions as FilterOptions;
use Magento\Framework\App\Action\Action;

/**
 * Class to process the filter options 
 */
class DealerSalesRankFilterOptions extends Action
{
    protected FilterOptions $filterOptions;

    /**
     * Constructor function
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param FilterOptions $filteroptions
     */
    public function __construct(
        Context  $context,
        JsonFactory $resultJsonFactory,
        FilterOptions $filteroptions
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filterOptions = $filteroptions;
        parent::__construct($context);
    }

    /**
     * Dealerrank filter option action
     *
     * @return array
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $data = [];
            $params = $this->getRequest()->getParams();
            if (isset($params['brands']) && isset($params['region'])) {
                $data = $this->filterOptions->getDealerAjax($params);
            } elseif (isset($params['brands'])) {
                $brands = $params['brands'];
                $data = $this->filterOptions->getRegionAjax($brands);
            }
            $resultJson->setData($data);
        }
        return $resultJson;
    }
}
