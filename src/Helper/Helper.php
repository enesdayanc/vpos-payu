<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 24/08/2017
 * Time: 10:23
 */

namespace PaymentGateway\VPosPayU\Helper;


use Exception;
use PaymentGateway\VPosPayU\Constant\PayUResponseReturnCode;
use PaymentGateway\VPosPayU\Constant\PayUResponseStatus;
use PaymentGateway\VPosPayU\Constant\RefundResponseMessage;
use PaymentGateway\VPosPayU\Exception\ValidationException;
use PaymentGateway\VPosPayU\Response\Response;
use PaymentGateway\VPosPayU\Setting\Setting;
use PayU\Alu\MerchantConfig;
use ReflectionClass;
use SimpleXMLElement;
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

        $response->setRawData(json_encode($payUResponse->getResponseParams()));

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

    public static function amountParser($amount)
    {
        return sprintf("%.2f", $amount);
    }

    public static function generateHash($key, $data)
    {
        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*", md5($k_ipad . $data)));
    }

    public static function ConvertRefundGuzzleResponseToResponse($guzzleResponse)
    {
        $response = new Response();

        $response->setRawData($guzzleResponse);

        try {
            $data = new SimpleXMLElement($guzzleResponse);
        } catch (Exception $exception) {
            throw new ValidationException('Invalid Xml Response', 'INVALID_XML_RESPONSE');
        }

        if (isset($data[0]) && strpos($data[0], '|') !== false) {
            $explodeArray = explode('|', $data[0]);

            $returnArray['ORDER_REF'] = $explodeArray[0];
            $returnArray['RESPONSE_CODE'] = $explodeArray[1];
            $returnArray['RESPONSE_MSG'] = $explodeArray[2];
            $returnArray['IRN_DATE'] = $explodeArray[3];
            $returnArray['ORDER_HASH'] = $explodeArray[4];


            if ($returnArray['RESPONSE_MSG'] == RefundResponseMessage::OK) {
                $response->setSuccessful(true);
            } else {
                $response->setErrorCode($returnArray['RESPONSE_CODE']);
                $response->setErrorMessage($returnArray['RESPONSE_MSG']);
            }

            $response->setTransactionReference($returnArray['ORDER_HASH']);

        }

        return $response;
    }

    public static function maskValue($value, $takeStart = 0, $takeStop = 0, $maskingCharacter = '*')
    {
        return substr($value, $takeStart, $takeStop) . str_repeat($maskingCharacter, strlen($value) - ($takeStop - $takeStart));
    }
}