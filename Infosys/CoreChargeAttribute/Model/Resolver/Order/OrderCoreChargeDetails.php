<?php

/**
 * @package     Infosys/CoreChargeAttribute
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CoreChargeAttribute\Model\Resolver\Order;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use \Magento\Framework\Serialize\Serializer\Json;

/**
 * Class to update core charge details in the customer orders query
 */
class OrderCoreChargeDetails implements ResolverInterface
{

    /**
     * @var Json
     */
    protected $json;

    /**
     * Constructor function
     *
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    /**
     * Get order item attribute value
     *
     * @param object $field
     * @param object $context
     * @param object $info
     * @param array $value
     * @param array $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $order =  $value['model'];
        if ($order) {
            $output = [];
            $core_charge_details = $order->getData($field->getName());
            if ($core_charge_details) {
                $coreChargeDetails = $this->getJsonDecode($core_charge_details);
                $individual = $coreChargeDetails["Individual"];
                if (count($individual) != 0) {
                    $totalCoreCharge = $coreChargeDetails["totalCoreCharge"];
                    foreach ($individual as $product) {
                        if(isset($product["PartNumber"])){
                            $partnumber = $product["PartNumber"];
                        }
                        else{
                            $partnumber = null;
                        }
                        $arr[] = [
                            'sku' => $product["SKU"],
                            'part_number' => $partnumber,
                            'quantity' => intval($product["Quantity"]),
                            'core_charge' => $product["Core_charge"]
                        ];
                    }

                    $output = [
                        'totalCoreCharge' => $totalCoreCharge,
                        'individual' => $arr
                    ];
                } else {
                    $output = [
                        'totalCoreCharge' => 0,
                        'individual' => []
                    ];
                }

                return $output;
            }
        }
    }

    /**
     * Json data
     *
     * @param array $response
     * @return array
     */
    public function getJsonDecode($response)
    {
        return $this->json->unserialize($response); // it's same as like json_decode
    }

    /**
     * Json data
     *
     * @param array $response
     * @return string
     */
    public function getJsonEncode($response)
    {
        return $this->json->serialize($response); // it's same as like json_encode
    }
}
