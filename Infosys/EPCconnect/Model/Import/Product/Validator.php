<?php
/**
 * @package Infosys/EPCconnect
 * @version 1.0.0
 * @author Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\EPCconnect\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Catalog\Model\Product\Attribute\Backend\Sku;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as RowValidatorInterface;

/**
 * Product import model validator
 *
 * @api
 * @since 100.0.2
 */
class Validator extends \Magento\CatalogImportExport\Model\Import\Product\Validator
{
    /**
     * @var RowValidatorInterface[]|AbstractValidator[]
     */
    protected $validators = [];

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $context;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var array
     */
    protected $_uniqueAttributes;

    /**
     * @var array
     */
    protected $_rowData;

    /**
     * @var string|null
     * @since 100.1.0
     */
    protected $invalidAttribute;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected $_attributeOptionManagement;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    protected $attributeOptionInterfaceFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param RowValidatorInterface[] $validators
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $attributeOptionInterfaceFactory,
        CoreSession $coreSession,
        $validators = []
    ) {
        $this->string = $string;
        $this->validators = $validators;
        $this->_attributeOptionManagement = $attributeOptionManagement;
        $this->attributeOptionInterfaceFactory = $attributeOptionInterfaceFactory;
        $this->_coreSession = $coreSession;
    }

    /**
     * Text validation
     *
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function textValidation($attrCode, $type)
    {
        $val = $this->string->cleanString($this->_rowData[$attrCode]);
        if ($type == 'text') {
            $valid = $this->string->strlen($val) < Product::DB_MAX_TEXT_LENGTH;
        } elseif ($attrCode == Product::COL_SKU) {
            $valid = $this->string->strlen($val) <= SKU::SKU_MAX_LENGTH;
            if ($this->string->strlen($val) !== $this->string->strlen(trim($val))) {
                $this->_addMessages([RowValidatorInterface::ERROR_SKU_MARGINAL_WHITESPACES]);
                return false;
            }
        } else {
            $valid = $this->string->strlen($val) < Product::DB_MAX_VARCHAR_LENGTH;
        }
        if (!$valid) {
            $this->_addMessages([RowValidatorInterface::ERROR_EXCEEDED_MAX_LENGTH]);
        }
        return $valid;
    }

    /**
     * Check if value is valid attribute option
     *
     * @param string $attrCode
     * @param array $possibleOptions
     * @param string $value
     * @return bool
     */
    private function validateOption($attrCode, $possibleOptions, $value)
    {
        $this->_coreSession->start();
        $options = $this->_coreSession->getData('updateOptions');
        
        if (!is_array($options) || !isset($options[$attrCode])) {
            $options[$attrCode] = [];
        }
        
        if (!isset($possibleOptions[strtolower($value)]) && !in_array($value, $options[$attrCode])) {
            $option = $this->attributeOptionInterfaceFactory->create();
            $option->setLabel($value);
            $option->setIsDefault(false);
            $option_id = $this->_attributeOptionManagement->add(
                \Magento\Catalog\Model\Product::ENTITY,
                $attrCode,
                $option
            );

            array_push($options[$attrCode], $value);
            array_unique($options[$attrCode]);
            $this->_coreSession->setData('updateOptions', $options);
        }
        
        return true;
    }

    /**
     * Numeric validation
     *
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function numericValidation($attrCode, $type)
    {
        $val = trim($this->_rowData[$attrCode]);
        if ($type == 'int') {
            $valid = (string)(int)$val === $val;
        } else {
            $valid = is_numeric($val);
        }
        if (!$valid) {
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE),
                        $attrCode,
                        $type
                    )
                ]
            );
        }
        return $valid;
    }

    /**
     * Is required attribute valid
     *
     * @param string $attrCode
     * @param array $attributeParams
     * @param array $rowData
     * @return bool
     */
    public function isRequiredAttributeValid($attrCode, array $attributeParams, array $rowData)
    {
        $doCheck = false;
        if ($attrCode == Product::COL_SKU) {
            $doCheck = true;
        } elseif ($attrCode == 'price') {
            $doCheck = false;
        } elseif ($attributeParams['is_required'] && $this->getRowScope($rowData) == Product::SCOPE_DEFAULT
            && $this->context->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE
        ) {
            $doCheck = true;
        }

        if ($doCheck === true) {
            return isset($rowData[$attrCode])
                && strlen(trim($rowData[$attrCode]))
                && trim($rowData[$attrCode]) !== $this->context->getEmptyAttributeValueConstant();
        }
        return true;
    }

    /**
     * Is attribute valid
     *
     * @param string $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isAttributeValid($attrCode, array $attrParams, array $rowData)
    {
        $this->_rowData = $rowData;
        if (isset($rowData['product_type']) && !empty($attrParams['apply_to'])
            && !in_array($rowData['product_type'], $attrParams['apply_to'])
        ) {
            return true;
        }

        if (!$this->isRequiredAttributeValid($attrCode, $attrParams, $rowData)) {
            $valid = false;
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(RowValidatorInterface::ERROR_VALUE_IS_REQUIRED),
                        $attrCode
                    )
                ]
            );
            return $valid;
        }

        if (!strlen(trim($rowData[$attrCode]))) {
            return true;
        }

        if ($rowData[$attrCode] === $this->context->getEmptyAttributeValueConstant() && !$attrParams['is_required']) {
            return true;
        }

        switch ($attrParams['type']) {
            case 'varchar':
            case 'text':
                $valid = $this->textValidation($attrCode, $attrParams['type']);
                break;
            case 'decimal':
            case 'int':
                $valid = $this->numericValidation($attrCode, $attrParams['type']);
                break;
            case 'select':
            case 'boolean':
                $valid = $this->validateOption($attrCode, $attrParams['options'], $rowData[$attrCode]);
                break;
            case 'multiselect':
                $values = $this->context->parseMultiselectValues($rowData[$attrCode]);
                foreach ($values as $value) {
                    $valid = $this->validateOption($attrCode, $attrParams['options'], $value);
                    if (!$valid) {
                        break;
                    }
                }

                $uniqueValues = array_unique($values);
                if (count($uniqueValues) != count($values)) {
                    $valid = false;
                    $this->_addMessages([RowValidatorInterface::ERROR_DUPLICATE_MULTISELECT_VALUES]);
                }
                break;
            case 'datetime':
                $val = trim($rowData[$attrCode]);
                $valid = strtotime($val) !== false;
                if (!$valid) {
                    $this->_addMessages([RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE]);
                }
                break;
            default:
                $valid = true;
                break;
        }

        if ($valid && !empty($attrParams['is_unique'])) {
            if (isset($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]])
                && ($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] != $rowData[Product::COL_SKU])) {
                $this->_addMessages([RowValidatorInterface::ERROR_DUPLICATE_UNIQUE_ATTRIBUTE]);
                return false;
            }
            $this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] = $rowData[Product::COL_SKU];
        }

        if (!$valid) {
            $this->setInvalidAttribute($attrCode);
        }

        return (bool)$valid;
    }

    /**
     * Set invalid attribute
     *
     * @param string|null $attribute
     * @return void
     * @since 100.1.0
     */
    protected function setInvalidAttribute($attribute)
    {
        $this->invalidAttribute = $attribute;
    }

    /**
     * Get invalid attribute
     *
     * @return string
     * @since 100.1.0
     */
    public function getInvalidAttribute()
    {
        return $this->invalidAttribute;
    }

    /**
     * Is valid attributes
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function isValidAttributes()
    {
        $this->_clearMessages();
        $this->setInvalidAttribute(null);
        if (!isset($this->_rowData['product_type'])) {
            return false;
        }
        $entityTypeModel = $this->context->retrieveProductTypeByName($this->_rowData['product_type']);
        if ($entityTypeModel) {
            foreach ($this->_rowData as $attrCode => $attrValue) {
                $attrParams = $entityTypeModel->retrieveAttributeFromCache($attrCode);
                if ($attrParams) {
                    $this->isAttributeValid($attrCode, $attrParams, $this->_rowData);
                }
            }
            if ($this->getMessages()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->_rowData = $value;
        $this->_clearMessages();
        $returnValue = $this->isValidAttributes();
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($value)) {
                $returnValue = false;
                $this->_addMessages($validator->getMessages());
            }
        }
        return $returnValue;
    }

    /**
     * Obtain scope of the row from row data.
     *
     * @param array $rowData
     * @return int
     */
    public function getRowScope(array $rowData)
    {
        if (empty($rowData[Product::COL_STORE])) {
            return Product::SCOPE_DEFAULT;
        }
        return Product::SCOPE_STORE;
    }

    /**
     * Init
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product $context
     * @return $this
     */
    public function init($context)
    {
        $this->context = $context;
        foreach ($this->validators as $validator) {
            $validator->init($context);
        }

        return $this;
    }
}
