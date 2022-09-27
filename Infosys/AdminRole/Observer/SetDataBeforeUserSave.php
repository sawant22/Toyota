<?php
/**
 * @package     Infosys/AdminRole
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\AdminRole\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetDataBeforeUserSave implements ObserverInterface
{
    /**
     * Prepare user object website data before saving
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        $userWebsites = $object->getWebsiteIds();
        if ($object->getAllWebsite()) {
            $object->setWebsiteIds('');
        } elseif (is_array($userWebsites)) {
            $object->setWebsiteIds(implode(",", $userWebsites));
        }

        return $this;
    }
}
