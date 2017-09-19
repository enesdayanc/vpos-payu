<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 19/09/2017
 * Time: 11:46
 */

namespace PaymentGateway\VPosPayU\Response;


class CardTokenInfoResponse
{
    private $tokenStatus;
    private $tokenExpirationDate;
    private $cardNumberMask;
    private $cardExpirationDate;
    private $cardHolderName;

    /**
     * @return mixed
     */
    public function getTokenStatus()
    {
        return $this->tokenStatus;
    }

    /**
     * @param mixed $tokenStatus
     */
    public function setTokenStatus($tokenStatus)
    {
        $this->tokenStatus = $tokenStatus;
    }

    /**
     * @return mixed
     */
    public function getTokenExpirationDate()
    {
        return $this->tokenExpirationDate;
    }

    /**
     * @param mixed $tokenExpirationDate
     */
    public function setTokenExpirationDate($tokenExpirationDate)
    {
        $this->tokenExpirationDate = $tokenExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getCardNumberMask()
    {
        return $this->cardNumberMask;
    }

    /**
     * @param mixed $cardNumberMask
     */
    public function setCardNumberMask($cardNumberMask)
    {
        $this->cardNumberMask = $cardNumberMask;
    }

    /**
     * @return mixed
     */
    public function getCardExpirationDate()
    {
        return $this->cardExpirationDate;
    }

    /**
     * @param mixed $cardExpirationDate
     */
    public function setCardExpirationDate($cardExpirationDate)
    {
        $this->cardExpirationDate = $cardExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getCardHolderName()
    {
        return $this->cardHolderName;
    }

    /**
     * @param mixed $cardHolderName
     */
    public function setCardHolderName($cardHolderName)
    {
        $this->cardHolderName = $cardHolderName;
    }
}