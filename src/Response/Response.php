<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 24/08/2017
 * Time: 13:27
 */

namespace PaymentGateway\VPosPayU\Response;


class Response
{
    private $successful = false;
    private $waiting = false;
    private $code;
    private $errorCode;
    private $errorMessage;
    private $transactionReference;
    private $isRedirect = false;
    private $redirectUrl;
    private $redirectMethod;
    private $redirectData;
    private $requestRawData;
    private $rawData;
    private $cardToken;
    private $cardPan;
    private $cardExpiryDate;
    private $cardTokenExpiryDate;
    private $cardHolderName;


    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    /**
     * @param bool $successful
     */
    public function setSuccessful(bool $successful)
    {
        $this->successful = $successful;
    }

    /**
     * @return bool
     */
    public function isWaiting(): bool
    {
        return $this->waiting;
    }

    /**
     * @param bool $waiting
     */
    public function setWaiting(bool $waiting)
    {
        $this->waiting = $waiting;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param mixed $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param mixed $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

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
     * @return bool
     */
    public function isRedirect(): bool
    {
        return $this->isRedirect;
    }

    /**
     * @param bool $isRedirect
     */
    public function setIsRedirect(bool $isRedirect)
    {
        $this->isRedirect = $isRedirect;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param mixed $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return mixed
     */
    public function getRequestRawData()
    {
        return $this->requestRawData;
    }

    /**
     * @param mixed $requestRawData
     */
    public function setRequestRawData($requestRawData)
    {
        $this->requestRawData = $requestRawData;
    }

    /**
     * @return mixed
     */
    public function getRedirectMethod()
    {
        return $this->redirectMethod;
    }

    /**
     * @param mixed $redirectMethod
     */
    public function setRedirectMethod($redirectMethod)
    {
        $this->redirectMethod = $redirectMethod;
    }

    /**
     * @return mixed
     */
    public function getRedirectData()
    {
        return $this->redirectData;
    }

    /**
     * @param mixed $redirectData
     */
    public function setRedirectData($redirectData)
    {
        $this->redirectData = $redirectData;
    }

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

    /**
     * @return mixed
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * @param mixed $rawData
     */
    public function setRawData($rawData)
    {
        $this->rawData = $rawData;
    }

    /**
     * @return mixed
     */
    public function getCardPan()
    {
        return $this->cardPan;
    }

    /**
     * @param mixed $cardPan
     */
    public function setCardPan($cardPan)
    {
        $this->cardPan = $cardPan;
    }

    /**
     * @return mixed
     */
    public function getCardExpiryDate()
    {
        return $this->cardExpiryDate;
    }

    /**
     * @param mixed $cardExpiryDate
     */
    public function setCardExpiryDate($cardExpiryDate)
    {
        $this->cardExpiryDate = $cardExpiryDate;
    }

    /**
     * @return mixed
     */
    public function getCardTokenExpiryDate()
    {
        return $this->cardTokenExpiryDate;
    }

    /**
     * @param mixed $cardTokenExpiryDate
     */
    public function setCardTokenExpiryDate($cardTokenExpiryDate)
    {
        $this->cardTokenExpiryDate = $cardTokenExpiryDate;
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