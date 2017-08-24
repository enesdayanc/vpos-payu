<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 23/08/2017
 * Time: 17:40
 */

namespace PaymentGateway\VPosPayU\Setting;

use PaymentGateway\VPosPayU\Constant\Platform;
use PaymentGateway\VPosPayU\Exception\NotFoundException;
use PaymentGateway\VPosPayU\Helper\Validator;

class Setting
{
    /** @var  Credential $credential */
    private $credential;
    private $threeDReturnUrl;
    private $defaultProductName;
    private $defaultProductCode;

    /**
     * @return Credential
     */
    public function getCredential(): Credential
    {
        return $this->credential;
    }

    /**
     * @param Credential $credential
     */
    public function setCredential(Credential $credential)
    {
        $this->credential = $credential;
    }

    /**
     * @return mixed
     */
    public function getThreeDReturnUrl()
    {
        return $this->threeDReturnUrl;
    }

    /**
     * @return mixed
     */
    public function getDefaultProductName()
    {
        return $this->defaultProductName;
    }

    /**
     * @param mixed $defaultProductName
     */
    public function setDefaultProductName($defaultProductName)
    {
        $this->defaultProductName = $defaultProductName;
    }

    /**
     * @return mixed
     */
    public function getDefaultProductCode()
    {
        return $this->defaultProductCode;
    }

    /**
     * @param mixed $defaultProductCode
     */
    public function setDefaultProductCode($defaultProductCode)
    {
        $this->defaultProductCode = $defaultProductCode;
    }

    /**
     * @param mixed $threeDReturnUrl
     */
    public function setThreeDReturnUrl($threeDReturnUrl)
    {
        $this->threeDReturnUrl = $threeDReturnUrl;
    }

    public function validate()
    {
        Validator::validateNotEmpty('credential', $this->getCredential());
        $this->getCredential()->validate();
        Validator::validateNotEmpty('threeDReturnUrl', $this->getThreeDReturnUrl());
        Validator::validateNotEmpty('defaultProductName', $this->getDefaultProductName());
        Validator::validateNotEmpty('defaultProductCode', $this->getDefaultProductCode());
    }

    public function getIrnUrl()
    {
        $this->validate();

        switch ($this->getCredential()->getPlatform()) {
            case Platform::RO:
                return 'https://secure.payu.ro/order/irn.php';
            case Platform::RU:
                return 'https://secure.payu.ru/order/irn.php';
            case Platform::UA:
                return 'https://secure.payu.ua/order/irn.php';
            case Platform::TR:
                return 'https://secure.payu.com.tr/order/irn.php';
            case Platform::HU:
                return 'https://secure.payu.hu/order/irn.php';
        }

        throw new NotFoundException('Irn Url Not Found', 'IRN_URL_NOT_FOUND');
    }
}