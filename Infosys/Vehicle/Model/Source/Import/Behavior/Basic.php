<?php
/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\Source\Import\Behavior;

use Magento\ImportExport\Model\Import;

/**
 * Basic class Import Behavior
 */
class Basic extends \Magento\ImportExport\Model\Source\Import\Behavior\Basic
{
    /**
     * Behavior options
     *
     * @return array
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_APPEND => __('Add/Update'),
            Import::BEHAVIOR_DELETE => __('Delete')
        ];
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getCode()
    {
        return 'vehicle';
    }
}
