<?php
/**
 * @package Infosys/CustomOrderNumber
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
declare(strict_types=1);

namespace Infosys\CustomOrderNumber\Plugin;

use \Magento\SalesSequence\Model\Manager as CoreManager;

/**
 * Class to change order sequence number irrespecive of store     
 */
class Manager
{
    /**
     * Change order sequence number irrespecive of store
     *
     * @param CoreManager $subject
     * @param string $entityType
     * @param int $storeId
     * @return array
     */
    public function beforeGetSequence(CoreManager $subject, $entityType, $storeId): array
    {
        if ($entityType == 'order') {
            $storeId = 0;
        }
        return [$entityType, $storeId];
    }
}
