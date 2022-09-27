<?php
/**
 * @package     Infosys/AttributeField
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\AttributeField\Plugin\Block\Adminhtml\Product\Attribute\Edit\Tab;

class Front
{
    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Constructor function
     *
     * @param Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Framework\Registry $registry
    ) {
        $this->_yesNo = $yesNo;
        $this->_coreRegistry = $registry;
    }

    /**
     * Get form HTML
     *
     * @param Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject
     * @param Closure $proceed
     * @return string
     */
    public function aroundGetFormHtml(
        \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front $subject,
        \Closure $proceed
    ) {
        $attributeObject = $this->_coreRegistry->registry('entity_attribute');
        $yesnoSource = $this->_yesNo->toOptionArray();
        $form = $subject->getForm();
        $fieldset = $form->getElement('front_fieldset');
        $fieldset->addField(
            'flag',
            'select',
            [
                'name' => 'flag',
                'label' => __('Auto Expanded'),
                'title' => __('Auto Expanded'),
                'note' => __('Flag for openning the filter in navigation block.'),
                'values' => $yesnoSource,
            ]
        );
        $form->setValues($attributeObject->getData());
        return $proceed();
    }
}
