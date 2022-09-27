<?php

/**
 * @package     Infosys/CheckoutVIN
 * @version     1.0.0
 * @author      Infosys Limited
 * @copyright   Copyright © 2021. All Rights Reserved.
 */

namespace Infosys\CheckoutVIN\Model\Resolver\Checkout;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Infosys\SearchByVIN\Model\Resolver\VehicleResolver;
use \Magento\Framework\Serialize\Serializer\Json;

use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class to update VIN data in quote on checkout
 */
class CheckoutVIN implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    protected $maskedQuoteIdToQuoteId;

    /**
     * @var VehicleResolver
     */
    protected $vehicleData;

    /**
     * @var Json
     */
    protected $json;

    /**
     * Constructor function
     *
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $quoteRepository
     * @param VehicleResolver $vehicleData
     * @param Json $json
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $quoteRepository,
        VehicleResolver $vehicleData,
        Json $json
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->vehicleData = $vehicleData;
        $this->json = $json;
    }
    /**
     * Get quote items attribute value
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
        $cartId = $args['input']['cartId'];
        $vin_numbers_list = $args['input']['vin_number'];
        $quoteId = $this->getQuoteIdFromMaskedHash($cartId);
        $quote = $this->quoteRepository->get($quoteId);
        $updated_vin_details = [];
        $valid_vin_details = [];
        $saved_vin_details_list = [];
        $saved_vin_details = $quote->getVinDetails();
        if ($saved_vin_details) {
            $saved_vin_details_list = $this->getJsonDecode($saved_vin_details);
            foreach ($saved_vin_details_list as $data) {
                $valid_vin_details[$data['vin_number']] = $data;
            }
        }
        foreach ($vin_numbers_list as $vin_number) {
            $vin_detail = [];
            if (!(key_exists($vin_number, $valid_vin_details))) {
                $vin_api_data = $this->vehicleData->getAttributes($vin_number);
                if (!isset($vin_api_data['allRecords'])) {
                    $vin_detail = [
                        [
                            'vin_number' => null,
                            'vehicle_name' => null,
                            'message' => "invalid vin number"
                        ]
                    ];
                    return $vin_detail;
                } else {
                    if (!isset($vin_api_data['allRecords'][0])) {
                        $vin_detail = [
                            [
                                'vin_number' => null,
                                'vehicle_name' => null,
                                'message' => "invalid vin number"
                            ]
                        ];
                        return $vin_detail;
                    } else {
                        $model_year = $vin_api_data['allRecords'][0]['model_year'];
                        $model_name = $vin_api_data['allRecords'][0]['model_name'];
                        $vehicle_name = $model_year.' '.$model_name;
                        $vin_detail = [
                            'vin_number' => $vin_number,
                            'vehicle_name' => $vehicle_name
                        ];
                        $updated_vin_details[] = $vin_detail;
                    }
                }
            } else {
                $updated_vin_details[] = $valid_vin_details[$vin_number];
            }
        }
        $vehicle_data = $this->getJsonEncode($updated_vin_details);
        $quote->setVinDetails($vehicle_data);
        $quote->save();
        return $updated_vin_details;
    }

    /**
     * Method to get quote Id from cart Id
     *
     * @param string $cartId
     * @return int
     */
    public function getQuoteIdFromMaskedHash($cartId)
    {
        return $this->maskedQuoteIdToQuoteId->execute($cartId);
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
