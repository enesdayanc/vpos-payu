<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 24/08/2017
 * Time: 10:23
 */

namespace PaymentGateway\VPosPayU\Helper;


use PaymentGateway\VPosPayU\Constant\PayUResponseReturnCode;
use PaymentGateway\VPosPayU\Constant\PayUResponseStatus;
use PaymentGateway\VPosPayU\Response\Response;
use PaymentGateway\VPosPayU\Setting\Setting;
use PayU\Alu\MerchantConfig;
use ReflectionClass;
use Spatie\ArrayToXml\ArrayToXml;

class Helper
{
    public static function arrayToXmlString(array $array)
    {
        return ArrayToXml::convert($array, 'posnetRequest', true, 'UTF-8');
    }

    public static function getConstants($class)
    {
        $oClass = new ReflectionClass ($class);
        return $oClass->getConstants();
    }

    /**
     * @param Setting $setting
     * @return MerchantConfig
     */
    public static function getMerchantConfigFromSetting(Setting $setting)
    {
        $setting->validate();

        $credential = $setting->getCredential();

        return new MerchantConfig($credential->getMerchantCode(), $credential->getSecretKey(), $credential->getPlatform());
    }

    /**
     * @param \PayU\Alu\Response $payUResponse
     * @param $requestRawData
     * @return Response
     */
    public static function ConvertPayUResponseToResponse($payUResponse, $requestRawData)
    {
        $response = new Response();

        $response->setRequestRawData($requestRawData);

        $response->setSuccessful(($payUResponse->getStatus() == PayUResponseStatus::SUCCESS && $payUResponse->getReturnCode() == PayUResponseReturnCode::AUTHORIZED));

        $response->setCode($payUResponse->getAuthCode());

        if ($payUResponse->getReturnCode() != PayUResponseReturnCode::AUTHORIZED) {
            $response->setErrorCode($payUResponse->getReturnCode());
        }

        if ($payUResponse->getReturnCode() != PayUResponseReturnCode::AUTHORIZED) {
            $response->setErrorMessage($payUResponse->getReturnMessage());
        }

        $response->setTransactionReference($payUResponse->getRefno());

        if ($payUResponse->isThreeDs()) {
            $response->setIsRedirect(true);
            $response->setRedirectUrl($payUResponse->getThreeDsUrl());
        }

        if (!empty($payUResponse->getTokenHash())) {
            $response->setCardToken($payUResponse->getTokenHash());
        }

        return $response;
    }
}