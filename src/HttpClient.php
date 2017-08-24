<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 24/08/2017
 * Time: 13:26
 */

namespace PaymentGateway\VPosPayU;


use PaymentGateway\VPosPayU\Constant\PayUResponseReturnCode;
use PaymentGateway\VPosPayU\Constant\PayUResponseStatus;
use PaymentGateway\VPosPayU\Constant\RedirectFormMethod;
use PaymentGateway\VPosPayU\Exception\CurlException;
use PaymentGateway\VPosPayU\Exception\ValidationException;
use PaymentGateway\VPosPayU\Helper\Helper;
use PaymentGateway\VPosPayU\Request\RequestInterface;
use PaymentGateway\VPosPayU\Response\Response;
use PaymentGateway\VPosPayU\Setting\Setting;
use PayU\Alu\Client;

class HttpClient
{
    private $setting;

    /**
     * HttpClient constructor.
     * @param $setting
     */
    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }


    public function sendPay(RequestInterface $requestElements)
    {
        $merchantConfig = Helper::getMerchantConfigFromSetting($this->setting);

        $request = $requestElements->getRequest($this->setting);

        $client = new Client($merchantConfig);

        try {
            $payUResponse = $client->pay($request);

        } catch (\Exception $exception) {
            throw new CurlException('Connection Error', $exception->getMessage());
        }

        return Helper::ConvertPayUResponseToResponse($payUResponse, json_encode($request->getRequestParams()));
    }
}