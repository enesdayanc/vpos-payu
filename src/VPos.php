<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 23/08/2017
 * Time: 17:39
 */

namespace PaymentGateway\VPosPayU;

use PaymentGateway\VPosPayU\Model\ThreeDResponse;
use PaymentGateway\VPosPayU\Request\PurchaseRequest;
use PaymentGateway\VPosPayU\Request\RefundRequest;
use PaymentGateway\VPosPayU\Setting\Setting;

class VPos
{
    /** @var  Setting $setting */
    private $setting;
    /** @var  HttpClient */
    private $httpClient;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
        $this->setting->validate();


        $this->httpClient = new HttpClient($setting);
    }

    public function purchase(PurchaseRequest $purchaseRequest)
    {
        return $this->httpClient->sendPay($purchaseRequest);
    }

    public function refund(RefundRequest $refundRequest)
    {
        return $this->httpClient->sendRefund($refundRequest);
    }

    public function handle3DResponse(array $allParams, $orderId)
    {
        $threeDResponse = new ThreeDResponse();

        return $threeDResponse->getResponseClass($this->setting, $allParams, $orderId);
    }

    /**
     * @return Setting
     */
    public function getSetting(): Setting
    {
        return $this->setting;
    }
}