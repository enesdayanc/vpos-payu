<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 24/08/2017
 * Time: 10:23
 */

namespace PaymentGateway\VPosPayU\Helper;


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
}