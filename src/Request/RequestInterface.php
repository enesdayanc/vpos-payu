<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 22/08/2017
 * Time: 10:27
 */

namespace PaymentGateway\VPosPayU\Request;


use PaymentGateway\VPosPayU\Setting\Setting;

interface RequestInterface
{
    public function validate();

}