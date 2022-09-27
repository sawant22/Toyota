<?php

/**
 * @package     Infosys/AdminRole
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\AdminRole\Block\User\Edit\Tab;

use Magento\Store\Model\ResourceModel\Website\Collection as WebsiteCollection;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory as StoreGroupCollection;
use Magento\Store\Model\ResourceModel\Store\Collection as StoreCollection;
use Magento\Backend\Block\Widget\Form;

class Main extends \Magento\User\Block\User\Edit\Tab\Main
{
    /**
     * @var WebsiteCollection
     */
    protected $websiteCollection;
    /**
     * @var StoreGroupCollection
     */
    protected $storeGroupCollection;
    /**
     * @var StoreCollection
     */
    protected $storeCollection;

    /**
     * Constructor function
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param WebsiteCollection $websiteCollection
     * @param StoreGroupCollection $storeGroupCollection
     * @param StoreCollection $storeCollection
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        WebsiteCollection $websiteCollection,
        StoreGroupCollection $storeGroupCollection,
        StoreCollection $storeCollection
    ) {
        $this->websiteCollection = $websiteCollection;
        $this->storeGroupCollection = $storeGroupCollection;
        $this->storeCollection = $storeCollection;
        parent::__construct($context, $registry, $formFactory, $authSession, $localeLists);
    }
    /**
     * Overriding the method to include user website fields
     *
     * @return Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $model = $this->_coreRegistry->registry('permissions_user');
        $baseFieldset = $form->getElement('base_fieldset');
        // field to check user have permission for all website or not
        $main = $baseFieldset->addField(
            "all_website",
            "select",
            [
                "label"     =>      __("User Website"),
                "class"     =>      "required-entry",
                "name"      =>      "all_website",
                "id"      =>      "all_website",
                "values"    =>      [
                    ["value" => 0, "label" => __("Custom")],
                    ["value" => 1, "label" => __("All")],
                ]
            ]
        );
        // depended field to add allowed user website
        $sub = $baseFieldset->addField(
            'website_ids',
            'multiselect',
            [
                'name' => 'website_ids',
                'label' => __('Website'),
                'id' => 'website_ids',
                'required' => 'true',
                'title' => __('Website'),
                'class' => 'input-select',
                'values' => $this->websiteCollection->toOptionArray()
            ]
        );
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
                ->addFieldMap($main->getHtmlId(), $main->getName())
                ->addFieldMap($sub->getHtmlId(), $sub->getName())
                ->addFieldDependence($sub->getName(), $main->getName(), 0)
        );
        $data = $model->getData();
        // set default value as 1 for 'all_website'
        if (!$model->getUserId()) {
            $data['all_website'] = '1';
        }
        unset($data['password']);
        unset($data[self::CURRENT_USER_PASSWORD_FIELD]);
        $form->setValues($data);
        $this->setForm($form);
        return $this;
    }
}
