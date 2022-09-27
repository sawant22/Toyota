<?php
/**
 * @package     Infosys/Vehicle
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\Vehicle\Model\Config\Backend;

/**
 * Class to provide import validation type
 */
class ValidationType
{
    /**
     * Validation type
     *
     * @return array
     */
    public function toOptionArray()
    {
        $types = [
            [
                'value' => 0,
                'label' => 'Stop On Error'
            ],
            [
                'value' => 1,
                'label' => 'Skip Error Entries'
            ]
        ];
        return $types;
    }
}
