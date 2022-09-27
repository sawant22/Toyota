<?php
/**
 * @package     Infosys/ShippingRestriction
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\ShippingRestriction\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Customer address region field renderer
 */
class Region extends \Magento\Customer\Block\Adminhtml\Edit\Renderer\Region
{

    /**
     * @var region
     */
    protected $region;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param \Infosys\ShippingRestriction\Helper\Region $region
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        \Infosys\ShippingRestriction\Helper\Region $region
    ) {
        parent::__construct($context, $directoryHelper, $data, $secureRenderer);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $this->region = $region;
    }

    /**
     * Output the region element and javasctipt that makes it dependent from country element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($country = $element->getForm()->getElement('country_id')) {
            $countryId = $country->getValue();
        } else {
            return $element->getDefaultHtml();
        }

        $regionId = $element->getForm()->getElement('region_id')->getValue();

        $html = '<div class="field field-state admin__field">';
        $element->setClass('input-text admin__control-text');
        $element->setRequired(true);
        $html .= $element->getLabelHtml() . '<div class="control admin__field-control">';
        $html .= $element->getElementHtml();

        $selectName = str_replace('region', 'region_id', $element->getName());
        $selectId = $element->getHtmlId() . '_id';
        $html .= '<select id="' .
            $selectId .
            '" name="' .
            $selectName .
            '" class="select required-entry admin__control-select">';
        $html .= '<option value="">' . __('Please select') . '</option>';
        $html .= '</select>';
         
        $scriptString = "\ndocument.querySelector('#$selectId').style.display = 'none';\n";
        $scriptString .= 'require(["prototype", "mage/adminhtml/form"], function(){';
        $scriptString .= '$("' . $selectId . '").setAttribute("defaultValue", "' . $regionId . '");' . "\n";
        $scriptString .= 'new regionUpdater("' .
            $country->getHtmlId() .
            '", "' .
            $element->getHtmlId() .
            '", "' .
            $selectId .
            '", ' .
            $this->region->getRegionJson($selectId) .
            ');' .
            "\n";

        $scriptString .= '});';
        $scriptString .= "\n";
        $html .= $this->secureRenderer->renderTag('script', [], $scriptString, false);

        $html .= '</div></div>' . "\n";

        return $html;
    }
}
