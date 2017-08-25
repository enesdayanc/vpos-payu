<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 02/08/2017
 * Time: 14:32
 */

namespace PaymentGateway\VPosPayU\Model;


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
     * @return mixed
     */
    public function getCreditCardNumber()
    {
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
     * @return mixed
     */
    public function getExpiryMonth()
    {
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
     * @return mixed
     */
    public function getExpiryYear()
    {
        return $this->expiryYear;
    }

    /**
     * @return mixed
     */
    public function getExpiryFullYear()
    {
        return '20' . $this->expiryYear;
    }

    /**
     * @param mixed $expiryYear
     */
    public function setExpiryYear($expiryYear)
    {
        $this->expiryYear = $expiryYear;
    }

    /**
     * @return mixed
     */
    public function getCvv()
    {
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