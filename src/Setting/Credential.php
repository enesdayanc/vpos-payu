<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 22/08/2017
 * Time: 10:29
 */

namespace PaymentGateway\VPosPayU\Setting;

use PaymentGateway\VPosPayU\Helper\Validator;

class Credential
{
    private $platform;
    private $merchantCode;
    private $secretKey;

    /**
     * @return mixed
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param mixed $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return mixed
     */
    public function getMerchantCode()
    {
        return $this->merchantCode;
    }

    /**
     * @param mixed $merchantCode
     */
    public function setMerchantCode($merchantCode)
    {
        $this->merchantCode = $merchantCode;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param mixed $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function validate()
    {
        Validator::validateNotEmpty('platform', $this->getPlatform());
        Validator::validateNotEmpty('merchantCode', $this->getMerchantCode());
        Validator::validateNotEmpty('secretKey', $this->getSecretKey());
    }
}