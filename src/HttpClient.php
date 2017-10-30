<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 24/08/2017
 * Time: 13:26
 */

namespace PaymentGateway\VPosPayU;


use Exception;
use PaymentGateway\VPosPayU\Exception\CurlException;
use PaymentGateway\VPosPayU\Helper\Helper;
use PaymentGateway\VPosPayU\Request\CardTokenInfoRequest;
use PaymentGateway\VPosPayU\Request\RefundRequest;
use PaymentGateway\VPosPayU\Request\RequestInterface;
use PaymentGateway\VPosPayU\Setting\Setting;
use PayU\Alu\Client;

class HttpClient
{
    private $setting;
    private $timeout = 20;

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

        return Helper::ConvertPayUResponseToResponse($payUResponse, json_encode($request->getRequestParams()), $this->setting, $requestElements);
    }


    public function sendRefund(RefundRequest $refundRequest)
    {
        $params = $refundRequest->getRequestParams($this->setting);

        $guzzleClient = new \GuzzleHttp\Client();

        try {
            $clientResponse = $guzzleClient->post(
                $this->setting->getIrnUrl(),
                [
                    'timeout' => $this->timeout,
                    'form_params' => $params
                ]
            );
        } catch (Exception $exception) {
            throw new CurlException('Connection Error', $exception->getMessage());
        }

        return Helper::ConvertRefundGuzzleResponseToResponse($clientResponse->getBody()->getContents());
    }

    /**
     * @param CardTokenInfoRequest $cardTokenInfoRequest
     * @return Response\CardTokenInfoResponse
     * @throws CurlException
     */
    public function getCardTokenInfo(CardTokenInfoRequest $cardTokenInfoRequest)
    {
        $guzzleClient = new \GuzzleHttp\Client();

        try {
            $clientResponse = $guzzleClient->get(
                $this->setting->getCardTokenInfoUrl($cardTokenInfoRequest->getCardToken()),
                [
                    'timeout' => $this->timeout,
                    'form_params' => $cardTokenInfoRequest->toArray($this->setting)
                ]
            );
        } catch (Exception $exception) {
            throw new CurlException('Connection Error', $exception->getMessage());
        }

        return Helper::ConvertCardTokenInfoGuzzleResponseToCardTokenInfoResponse($clientResponse->getBody()->getContents());
    }
}