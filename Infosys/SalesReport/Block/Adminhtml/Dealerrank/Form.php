<?php
/**
 * @package Infosys/SalesReport
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\SalesReport\Block\Adminhtml\Dealerrank;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Model\UrlInterface;
use Infosys\SalesReport\Model\DealerSalesRankFilterOptions; 


/**
 * Adminhtml report filter form
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * Report field options
     *
     * @var array
     */
    protected $filterOptions;

    /**
     * @var BackendSession
     */
    private $backendSession;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,  
        DealerSalesRankFilterOptions $filteroptions, 
        UrlInterface $backendUrl,
        array $data = []
    ){
        $this->filterOptions = $filteroptions;
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Add fieldset for dealer rank form fields
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/index');
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'dealerrank_filter_form',
                    'action' => $actionUrl,
                    'method' => 'get'
                ]
            ]
        );

        $htmlIdPrefix = 'sales_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Filter')]);
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField('url', 'hidden', ['name' => 'url', 'value'=>$actionUrl]);

        $fieldset->addField(
            'brand',
            'multiselect',
            [
                'label' => __('Brand'),
                'title' => __('Brand'),
                'name' => 'brand',
                'onchange' => 'getRegion()',
                'required' => true,
                'values' => $this->filterOptions->getBrand()
            ]
        )->setAfterElementHtml($this->_setBrandHtml());

        $fieldset->addField(
            'region',
            'select',
            [
                'name' => 'region',
                'options' => $this->filterOptions->getRegion(),
                'label' => __('Region'),
                'onchange' => 'getDealer()',
                'required' => true,
                'title' => __('Region ')
            ]
        )->setAfterElementHtml($this->_setRegionHtml());

        $fieldset->addField(
            'dealer',
            'select',
            [
                'name' => 'dealer',
                'options' =>  $this->filterOptions->getDealer(),
                'label' => __('Dealer'),
                'required' => true,
                'title' => __('Dealer')
            ]
        ); 

        $fieldset->addField(
            'from',
            'date',
            [
                'name' => 'from',
                'date_format' => $dateFormat,
                'label' => __('From'),
                'title' => __('From'),
                'css_class' => 'admin__field-small',
                'class' => 'admin__control-text'
            ]
        );

        $fieldset->addField(
            'to',
            'date',
            [
                'name' => 'to',
                'date_format' => $dateFormat,
                'label' => __('To'),
                'title' => __('To'),
                'css_class' => 'admin__field-small',
                'class' => 'admin__control-text'
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Initialize form fields values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return \Magento\Backend\Block\Widget\Form
    */
    protected function _initFormValues()
    {
        $data = $this->filterOptions->filterData();
        if($data)
        {
            foreach ($data as $key => $value) {
                if (is_array($value) && isset($value[0])) {
                    $data[$key] = explode(',', $value[0]);
                }
            }
            $this->getForm()->addValues($data);
        }
        return parent::_initFormValues();
    }

    /**
     * Add HTML AJAX for brand
     *
     * @return HTML
    */
    protected function _setBrandHtml()
    {
        $url = $this->backendUrl->getUrl("*/*/dealersalesrankfilteroptions/");
        return '<script>
            function getRegion (values) {
                var selBrands = jQuery("#sales_report_brand").val();
                if(selBrands != null) {
                selBrands = selBrands.toString();
                jQuery.ajax({
                    showLoader: true, 
                    url: "'. $url.'", 
                    data: {"brands":selBrands},
                    type: "POST", 
                    dataType: "json"
                }).success(function (response) { 
                    jQuery("#sales_report_region").empty();
                    jQuery.each( response, function( key, value ) {
                        if(key == "region" && !jQuery.isEmptyObject(value))
                        {
                            jQuery.each( value, function( key, value ) {
                                jQuery("#sales_report_region").append(jQuery("<option></option>").attr("value", key).text(value))
                            });    
                        }
                        if(key == "dealers" && !jQuery.isEmptyObject(value))
                        {
                            jQuery("#sales_report_dealer").empty();
                            jQuery.each( value, function( key, value ) {
                                jQuery("#sales_report_dealer").append(jQuery("<option></option>").attr("value", key).text(value))
                            });    
                        }
                    });
                });
            }
        }
        </script><style>#sales_report_brand {height:100px;}</style>';
    }

    /**
     * Add HTML AJAX for Region
     *
     * @return HTML
    */
    protected function _setRegionHtml()
    {
        $url = $this->backendUrl->getUrl("*/*/dealersalesrankfilteroptions/");
        return '<script>
        function getDealer(values) {
                var selBrands = jQuery("#sales_report_brand").val();
                var selRegion = jQuery("#sales_report_region").val();
                if(selBrands != null && selRegion != null) {
                selBrands = selBrands.toString();
                jQuery.ajax({
                    showLoader: true, 
                    url: "'. $url.'", 
                    data: {"brands":selBrands,"region":selRegion},
                    type: "POST", 
                    dataType: "json"
                }).success(function (response) { 
                    jQuery("#sales_report_dealer").empty();
                    jQuery.each( response, function( key, value ) {
                        jQuery("#sales_report_dealer").append(jQuery("<option></option>").attr("value", key).text(value)); 
                    });
                });
            }
        }
        </script>';
    }
}
