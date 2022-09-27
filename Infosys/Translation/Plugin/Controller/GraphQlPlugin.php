<?php
/**
 * @package     Infosys/Translation
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\Translation\Plugin\Controller;

/**
 * Class to inject translations to graphql
 */
class GraphQlPlugin
{
    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var State
     */
    private $appState;

    /**
     * Constructor function
     *
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Framework\App\State $appState
    ) {
        $this->areaList = $areaList;
        $this->appState = $appState;
    }

    /**
     * Method to inject translations to graphql
     *
     * @param \Magento\GraphQl\Controller\GraphQl $subject
     * @return void
     */
    public function beforeDispatch(\Magento\GraphQl\Controller\GraphQl $subject)
    {
        $area = $this->areaList->getArea($this->appState->getAreaCode());
        $area->load(\Magento\Framework\App\Area::PART_TRANSLATE);
    }
}
