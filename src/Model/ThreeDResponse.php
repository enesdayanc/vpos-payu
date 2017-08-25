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
     * @return Response
     */
    public function getResponseClass(Setting $setting, array $allParams)
    {
        $response = new Response();

        $merchantConfig = Helper::getMerchantConfigFromSetting($setting);

        $client = new Client($merchantConfig);

        $handleResponse = $client->handleThreeDSReturnResponse($allParams);

        if ($handleResponse->getStatus() == ThreeDSResponse::SUCCESS) {
            $response->setSuccessful(true);
        } else {
            $response->setErrorMessage($handleResponse->getReturnMessage());
        }

        $response->setCode($handleResponse->getAuthCode());
        $response->setCardToken($handleResponse->getTokenHash());
        $response->setTransactionReference($handleResponse->getRefno());

        return $response;
    }
}