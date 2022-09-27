<?php

/**
 * @package     Infosys/CreateWebsite
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CreateWebsite\Block\Store\Edit\Form;

use Infosys\CreateWebsite\Model\TRDFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\GroupFactory;

class Website extends \Magento\Backend\Block\System\Store\Edit\Form\Website
{
    /**
     * @var TRDFactory
     */
    protected $_trdFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param GroupFactory $groupFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GroupFactory $groupFactory,
        array $data = [],
        TRDFactory $trdFactory
    ) {
        $this->_trdFactory = $trdFactory;
        parent::__construct($context, $registry, $formFactory, $groupFactory, $data);
    }
    /**
     * Overriding method to include Dealer Code field in website form
     *
     * @return void
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $websiteModel = $this->_coreRegistry->registry('store_data');
        $postData = $this->_coreRegistry->registry('store_post_data');
        if ($postData) {
            $websiteModel->setData($postData['website']);
        }
        $form = $this->getForm();
        $fieldset = $form->getElement('website_fieldset');
        $fieldset->addField(
            'dealer_code',
            'text',
            [
                'name' => 'website[dealer_code]',
                'label' => __('Dealer Code'),
                'value' => $websiteModel->getDealerCode(),
                'required' => false,
                'class' => '',
                'disabled' => $websiteModel->isReadOnly()
            ]
        );

        $trd = $this->_trdFactory->create();
        $trdCollection = $trd->getCollection();
        foreach($trdCollection as $itemTRD){
            $trdData = $itemTRD->getData();
            $options[] = ['value' => $trdData['id'], 'label' => $trdData['region_label']];
        }

        $fieldset->addField(
            'region_id',
            'select',
            [
                'name' => 'website[region_id]',
                'label' => __('Region'),
                'title' => __('Region'),
                'values' => $options,
                'value' => $websiteModel->getRegionId(),
                'required' => false,
                'class' => '',
                'note' => __('Toyota Region note.'),
            ]
        );
        $this->setForm($form);
        return $this;
    }
}
