<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 02/08/2017
 * Time: 14:32
 */

namespace PaymentGateway\VPosPayU\Model;


use PaymentGateway\VPosPayU\Helper\Helper;
use PaymentGateway\VPosPayU\Helper\Validator;

class Card
{
    private $creditCardNumber;
    private $expiryMonth;
    private $expiryYear;
    private $cvv;
    private $firstName;
    private $lastName;
    private $cardToken;

    /**
     * @param bool $maskCardData
     * @return mixed
     */
    public function getCreditCardNumber(bool $maskCardData = false)
    {
        if ($maskCardData) {
            return Helper::maskValue($this->creditCardNumber, 0, 6);
        }

        return $this->creditCardNumber;
    }

    /**
     * @param mixed $creditCardNumber
     */
    public function setCreditCardNumber($creditCardNumber)
    {
        $this->creditCardNumber = $creditCardNumber;
    }

    /**
     * @param bool $maskCardData
     * @return mixed
     */
    public function getExpiryMonth(bool $maskCardData = false)
    {

        if ($maskCardData) {
            return Helper::maskValue($this->expiryMonth);
        }

        return $this->expiryMonth;
    }

    /**
     * @param mixed $expiryMonth
     */
    public function setExpiryMonth($expiryMonth)
    {
        $this->expiryMonth = $expiryMonth;
    }

    /**
     * @param bool $maskCardData
     * @return mixed
     */
    public function getExpiryYear(bool $maskCardData = false)
    {
        if ($maskCardData) {
            return Helper::maskValue($this->expiryYear);
        }

        return $this->expiryYear;
    }

    /**
     * @param bool $maskCardData
     * @return mixed
     */
    public function getExpiryFullYear(bool $maskCardData = false)
    {
        return '20' . $this->getExpiryYear($maskCardData);
    }

    /**
     * @param mixed $expiryYear
     */
    public function setExpiryYear($expiryYear)
    {
        $this->expiryYear = $expiryYear;
    }

    /**
     * @param bool $maskCardData
     * @return mixed
     */
    public function getCvv(bool $maskCardData = false)
    {
        if ($maskCardData) {
            return Helper::maskValue($this->cvv);
        }

        return $this->cvv;
    }

    /**
     * @param mixed $cvv
     */
    public function setCvv($cvv)
    {
        $this->cvv = $cvv;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }


    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @param bool $maskCardData
     * @return mixed
     */
    public function getCardToken(bool $maskCardData = false)
    {
        if ($maskCardData) {
            return Helper::maskValue($this->cardToken);
        }

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
        if (!empty($this->getCardToken())) {
            return true;
        }

        Validator::validateCardNumber($this->getCreditCardNumber());
        Validator::validateExpiryMonth($this->getExpiryMonth());
        Validator::validateExpiryYear($this->getExpiryYear());
        Validator::validateCvv($this->getCvv());
        Validator::validateNotEmpty('firstName', $this->getFirstName());
        Validator::validateNotEmpty('lastName', $this->getLastName());
    }
}
