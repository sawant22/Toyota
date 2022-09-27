<?php
/**
 * @package Infosys/EdamImageModifier
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\EdamImageModifier\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Event for gallery template
 */
class ChangeGalleryTemplate implements ObserverInterface
{
    /**
     * Load gallery template
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getBlock()->setTemplate('Infosys_EdamImageModifier::helper/gallery.phtml');
    }
}
