<?php
/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\SalesReport\Block\Adminhtml\Dealerrank;

use Infosys\SalesReport\Model\DealerSalesRank; 

/**
 * Backend grid container block
 */
class Grid extends \Magento\Backend\Block\Widget\Container
{

    protected $_template = 'Dealerrank/ReportGrid.phtml';

    protected $_exportTypes = [];

    /**
     * @var DealerSalesRank
    */
    private $dealerSalesRank;


    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        DealerSalesRank $dealerSalesRank,
        array $data = []
    ) {
        $this->dealerSalesRank = $dealerSalesRank;
        parent::__construct($context, $data);
        $this->addExportType('*/*/exporttocsv', __('CSV'));
        $this->addExportType('*/*/exporttoexcel', __('Excel XML'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'filtersubmit',
            'label' => __('Show Report'),
            'class' => 'primary',
            'onclick' => 'filterFormSubmit()'
        ];
        $this->buttonList->add('filter_form_submit', $addButtonProps);
        return parent::_prepareLayout();
    }

    public function addExportType($url, $label)
    {
        $this->_exportTypes[] = new \Magento\Framework\DataObject(
            ['url' => $this->getUrl($url, ['_current' => true]), 'label' => $label]
        );
        return $this;
    }

    public function getExportTypes()
    {
        return $this->_exportTypes;
    }

    /**
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * @return caclulated rank 
     */
    public function prepareRankData()
    {
        return $this->dealerSalesRank->calculateDealerRank();
    }
}