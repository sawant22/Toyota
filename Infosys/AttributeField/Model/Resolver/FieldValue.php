<?php
/**
 * @package     Infosys/AttributeField
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\AttributeField\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product\Attribute\Repository;

/**
 * Resolver for Getting Flag value in GraphQl
 */
class FieldValue implements ResolverInterface
{
    /**
     * @var Repository $attributeRepository
     */

    protected $attributeRepository;
    /**
     * Constructor function
     *
     * @param Repository $attributeRepository
     */
    
    public function __construct(Repository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Method to get flag option label
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return string
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $code = $value['attribute_code'];
        $attribute = $this->attributeRepository->get($code);
        $arr = $attribute->getData();
        return $arr['flag'];
    }
}
