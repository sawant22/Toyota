<?php
/**
 * @package     Infosys/CreateWebsite
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\CreateWebsite\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;

/**
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $regionId = $this->getRegionId();
        if ($regionId) {
            $data = [
                'label' => __('Delete Region'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->urlBuilder->getUrl('*/*/delete', ['id' => $regionId]) . '\', {data: {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Return the current Region Id.
     *
     * @return int|null
     */
    public function getRegionId()
    {
        $trd_id = $this->registry->registry('current_region_id');
        return $trd_id;
    }
}
