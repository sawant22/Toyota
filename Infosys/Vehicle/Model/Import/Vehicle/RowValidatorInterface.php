<?php
/**
 * @package Infosys/Vehicle
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\Vehicle\Model\Import\Vehicle;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    const ERROR_MODEL_YEAR_IS_EMPTY = 'ModelYearEmpty';
    const ERROR_MODEL_CODE_IS_EMPTY = 'ModelCodeEmpty';
    const ERROR_MODEL_YEAR_CODE_NOT_FOUND = 'ModelYearCodeNotFound';
    const ERROR_BRAND_IS_EMPTY = 'BrandEmpty';
    const ERROR_SERIES_NAME_IS_EMPTY = 'SeriesNameEmpty';
    const ERROR_GRADE_IS_EMPTY = 'GradeEmpty';
    const ERROR_DRIVELINE_IS_EMPTY = 'DrivelineEmpty';
    const ERROR_DUPLICATE_VEHICLE = 'DuplicateVehicle';
    const ERROR_DUPLICATE_YEAR_CODE = 'DuplicateYearCode';
    const WARNING_GRADE_EMPTY = 'Warning: Grade is empty after formatting.  Defaulting to NONE';
    const WARNING_DRIVELINE_EMPTY = 'Warning: Driveline is empty after formatting.  Defaulting to NONE';

    /**
     * Initialize validator
     *
     * @param mixed $context
     * @return $this
     */
    public function init($context);
}
