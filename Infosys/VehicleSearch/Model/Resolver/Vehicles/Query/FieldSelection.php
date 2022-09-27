<?php
/**
 * @package     Infosys/VehicleSearch
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

declare(strict_types=1);

namespace Infosys\VehicleSearch\Model\Resolver\Vehicles\Query;

use Magento\Framework\GraphQl\Query\FieldTranslator;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Extract requested fields from vehicles query
 */
class FieldSelection
{
    /**
     * @var FieldTranslator
     */
    private $fieldTranslator;

    /**
     * @param FieldTranslator $fieldTranslator
     */
    public function __construct(FieldTranslator $fieldTranslator)
    {
        $this->fieldTranslator = $fieldTranslator;
    }

    /**
     * Get requested fields from vehicles query
     *
     * @param ResolveInfo $resolveInfo
     * @return string[]
     */
    public function getVehiclesFieldSelection(ResolveInfo $resolveInfo): array
    {
        $vehicleFields = $resolveInfo->getFieldSelection(1);
        $sectionNames = ['items', 'vehicle'];

        $fieldNames = [];
        foreach ($sectionNames as $sectionName) {
            if (isset($vehicleFields[$sectionName])) {
                foreach (array_keys($vehicleFields[$sectionName]) as $fieldName) {
                    $fieldNames[] = $this->fieldTranslator->translate($fieldName);
                }
            }
        }
        return array_unique($fieldNames);
    }
}
