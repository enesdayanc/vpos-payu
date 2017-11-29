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
use PaymentGateway\VPosPayU\Constant\RedirectMethod;
use PaymentGateway\VPosPayU\Constant\RefundResponseMessage;
use PaymentGateway\VPosPayU\Exception\ValidationException;
use PaymentGateway\VPosPayU\HttpClient;
use PaymentGateway\VPosPayU\Request\CardTokenInfoRequest;
use PaymentGateway\VPosPayU\Request\PurchaseRequest;
use PaymentGateway\VPosPayU\Request\RequestInterface;
use PaymentGateway\VPosPayU\Response\CardTokenInfoResponse;
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
     * @param Setting $setting
     * @param RequestInterface $request
     * @return Response
     */
    public static function ConvertPayUResponseToResponse(
        $payUResponse,
        $requestRawData,
        Setting $setting,
        RequestInterface $request
    ) {
        $response = new Response();

        $response->setRawData(json_encode($payUResponse->getResponseParams()));

        $response->setRequestRawData($requestRawData);

        if (($payUResponse->getStatus() == PayUResponseStatus::SUCCESS && $payUResponse->getReturnCode() == PayUResponseReturnCode::AUTHORIZED)) {
            $response->setSuccessful(true);
            $response->setWaiting(true);
        }

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
            $response->setRedirectMethod(RedirectMethod::GET);
            $response->setRedirectData(null);
        }

        if (!empty($payUResponse->getTokenHash())) {

            $cardTokenInfoResponse = Helper::getCardTokenInfo($payUResponse->getTokenHash(), $setting);

            if (!empty($payUResponse->getAdditionalParameterValue('PAN'))) {
                $cardPan = $payUResponse->getAdditionalParameterValue('PAN');
            } elseif (!empty($cardTokenInfoResponse->getCardPan())) {
                $cardPan = $cardTokenInfoResponse->getCardPan();
            } elseif ($request instanceof PurchaseRequest) {
                $cardPan = self::getCardPanByCardNumber($request->getCard()->getCreditCardNumber());
            } else {
                $cardPan = "";
            }

            $response->setCardPan($cardPan);
            $response->setCardToken($payUResponse->getTokenHash());
            $response->setCardExpiryDate($cardTokenInfoResponse->getCardExpirationDate());
            $response->setCardTokenExpiryDate($cardTokenInfoResponse->getTokenExpirationDate());
            $response->setCardHolderName($cardTokenInfoResponse->getCardHolderName());
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

        $data = @simplexml_load_string($guzzleResponse);

        if (empty($data)) {
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
                $response->setWaiting(true);
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

    /**
     * @param string $cardToken
     * @param Setting $setting
     * @return CardTokenInfoResponse
     */
    public static function getCardTokenInfo(string $cardToken, Setting $setting)
    {
        $cardTokenInfoRequest = new CardTokenInfoRequest();

        $cardTokenInfoRequest->setCardToken($cardToken);

        $httpClient = new HttpClient($setting);

        return $httpClient->getCardTokenInfo($cardTokenInfoRequest);
    }

    /**
     * @param $guzzleResponse
     * @return CardTokenInfoResponse
     */
    public static function ConvertCardTokenInfoGuzzleResponseToCardTokenInfoResponse($guzzleResponse)
    {
        $cardTokenInfoResponse = new CardTokenInfoResponse();

        $dataArray = json_decode($guzzleResponse, true);

        if (!empty($dataArray['token'])) {
            $token = $dataArray['token'];

            if (!empty($token['tokenStatus'])) {
                $cardTokenInfoResponse->setTokenStatus($token['tokenStatus']);
            }

            if (!empty($token['tokenExpirationDate'])) {
                $cardTokenInfoResponse->setTokenExpirationDate(new \DateTime($token['tokenExpirationDate']));
            }

            if (!empty($token['cardNumberMask'])) {
                $cardTokenInfoResponse->setCardNumberMask($token['cardNumberMask']);
            }

            if (!empty($token['cardExpirationDate'])) {
                $cardTokenInfoResponse->setCardExpirationDate(new \DateTime($token['cardExpirationDate']));
            }

            if (!empty($token['cardHolderName'])) {
                $cardTokenInfoResponse->setCardHolderName($token['cardHolderName']);
            }

            if (!empty($token['cardNumberMask'])) {

                // 9999-99xx-xxxx-9999 => 9999-xxxx-xxxx-9999
                $cardPanValue = preg_replace("/-[0-9][0-9][a-zA-Z][a-zA-Z]-/", "-xxxx-", $token['cardNumberMask']);

                $cardTokenInfoResponse->setCardPan($cardPanValue);
            }
        }


        return $cardTokenInfoResponse;
    }

    public static function getCardPanByCardNumber(string $cardNumber)
    {
        Validator::validateCardNumber($cardNumber);

        return substr($cardNumber, 0, 4) . '-xxxx-xxxx-' . substr($cardNumber, -4, 4);
    }
}