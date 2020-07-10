<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 19/09/2017
 * Time: 10:55
 */

namespace PaymentGateway\VPosPayU\Request;


use PaymentGateway\VPosPayU\Helper\Validator;
use PaymentGateway\VPosPayU\Setting\Setting;

class CardTokenInfoRequest implements RequestInterface
{
    private $cardToken;

    /**
     * @return mixed
     */
    public function getCardToken()
    {
        return $this->cardToken;
    }

    /**
     * @param mixed $cardToken
     */
    public function setCardToken($cardToken)
    {
        $this->cardToken = $cardToken;
    }

    public function validate()
    {
        Validator::validateNotEmpty('cardToken', $this->getCardToken());
    }

    public function toArray(Setting $setting)
    {
        $this->validate();
        $setting->validate();

        $credential = $setting->getCredential();

        $payload = array(
            'merchant' => $credential->getMerchantCode(),
            'timestamp' => time()
        );

        ksort($payload);

        $hashString = '';
        foreach ($payload as $key => $value) {
            if ($key != 'timestamp') {
                $hashString .= $value;
            }
        }

        $hashString .= $payload['timestamp'];

        $payload['signature'] = hash_hmac('sha256', $hashString, $credential->getSecretKey());

        return $payload;
    }
}
