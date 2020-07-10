<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 25/08/2017
 * Time: 10:15
 */

namespace PaymentGateway\VPosPayU\Request;


use PaymentGateway\ISO4217\Model\Currency;
use PaymentGateway\VPosPayU\Helper\Helper;
use PaymentGateway\VPosPayU\Helper\Validator;
use PaymentGateway\VPosPayU\Setting\Setting;

class RefundRequest implements RequestInterface
{

    private $transactionReference;
    private $orderTotalAmount;
    /** @var  Currency */
    private $currency;
    private $amount;

    /**
     * @return mixed
     */
    public function getTransactionReference()
    {
        return $this->transactionReference;
    }

    /**
     * @param mixed $transactionReference
     */
    public function setTransactionReference($transactionReference)
    {
        $this->transactionReference = $transactionReference;
    }

    /**
     * @return mixed
     */
    public function getOrderTotalAmount()
    {
        return $this->orderTotalAmount;
    }

    /**
     * @param mixed $orderTotalAmount
     */
    public function setOrderTotalAmount($orderTotalAmount)
    {
        $this->orderTotalAmount = $orderTotalAmount;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function validate()
    {
        Validator::validateNotEmpty('transactionReference', $this->getTransactionReference());
        Validator::validateAmount($this->getAmount());
        Validator::validateAmount($this->getOrderTotalAmount());
        Validator::validateCurrency($this->getCurrency());
    }

    public function getRequestParams(Setting $setting)
    {
        $this->validate();

        $credential = $setting->getCredential();

        $postData = array(
            "MERCHANT" => $credential->getMerchantCode(),
            "ORDER_REF" => $this->getTransactionReference(),
            "ORDER_AMOUNT" => $this->getOrderTotalAmount(),
            "ORDER_CURRENCY" => $this->getCurrency()->getAlpha3(),
            "IRN_DATE" => gmdate('Y-m-d H:i:s'),
            "REGENERATE_CODES" => "",
            "LICENSE_HANDLING" => "",
            "AMOUNT" => Helper::amountParser($this->getAmount()),
        );

        $hashString = "";
        foreach ($postData as $key => $val) {
            $hashString .= strlen($val) . $val;
        }

        $postData["ORDER_HASH"] = Helper::generateHash($credential->getSecretKey(), $hashString);

        return $postData;
    }
}
