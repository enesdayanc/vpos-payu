<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 25/08/2017
 * Time: 15:11
 */

namespace PaymentGateway\VPosPayU\Model;


use PaymentGateway\VPosPayU\Constant\ThreeDSResponse;
use PaymentGateway\VPosPayU\Helper\Helper;
use PaymentGateway\VPosPayU\Response\Response;
use PaymentGateway\VPosPayU\Setting\Setting;
use PayU\Alu\Client;

class ThreeDResponse
{
    /**
     * @param Setting $setting
     * @param array $allParams
     * @param $orderId
     * @return Response
     */
    public function getResponseClass(Setting $setting, array $allParams, $orderId)
    {
        $response = new Response();
        $response->setRequestRawData(json_encode($allParams, true));


        $merchantConfig = Helper::getMerchantConfigFromSetting($setting);

        $client = new Client($merchantConfig);

        $handleResponse = $client->handleThreeDSReturnResponse($allParams);

        $response->setRawData(json_encode($handleResponse->getResponseParams(), true));

        if ($handleResponse->getOrderRef() == $orderId) {
            if ($handleResponse->getStatus() == ThreeDSResponse::SUCCESS) {
                $response->setSuccessful(true);
                $response->setWaiting(true);

                if (!empty($handleResponse->getTokenHash())) {

                    $cardTokenInfoResponse = Helper::getCardTokenInfo($handleResponse->getTokenHash(), $setting);

                    if (!empty($handleResponse->getAdditionalParameterValue('PAN'))) {
                        $cardPan = $handleResponse->getAdditionalParameterValue('PAN');
                    } elseif (!empty($cardTokenInfoResponse->getCardPan())) {
                        $cardPan = $cardTokenInfoResponse->getCardPan();
                    } else {
                        $cardPan = "";
                    }

                    $response->setCardPan($cardPan);
                    $response->setCardToken($handleResponse->getTokenHash());
                    $response->setCardExpiryDate($cardTokenInfoResponse->getCardExpirationDate());
                    $response->setCardTokenExpiryDate($cardTokenInfoResponse->getTokenExpirationDate());
                    $response->setCardHolderName($cardTokenInfoResponse->getCardHolderName());
                }

            } else {
                $response->setErrorMessage($handleResponse->getReturnMessage());
            }
        } else {
            $response->setErrorMessage('Order id not match');
        }

        $response->setCode($handleResponse->getAuthCode());

        $response->setTransactionReference($handleResponse->getRefno());

        return $response;
    }
}