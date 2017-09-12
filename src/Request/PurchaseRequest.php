<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 24/08/2017
 * Time: 10:54
 */

namespace PaymentGateway\VPosPayU\Request;


use PaymentGateway\ISO4217\Model\Currency;
use PaymentGateway\VPosPayU\Constant\PayMethod;
use PaymentGateway\VPosPayU\Helper\Helper;
use PaymentGateway\VPosPayU\Helper\Validator;
use PaymentGateway\VPosPayU\Model\Address;
use PaymentGateway\VPosPayU\Model\Card;
use PaymentGateway\VPosPayU\Setting\Setting;
use PayU\Alu\Billing;
use PayU\Alu\CardToken;
use PayU\Alu\Delivery;
use PayU\Alu\Order;
use PayU\Alu\Product;
use PayU\Alu\Request;
use PayU\Alu\User;

class PurchaseRequest implements RequestInterface
{
    private $orderId;
    private $userIp;
    private $amount;
    private $installment;
    /** @var  Currency */
    private $currency;
    /** @var  Address */
    private $billingAddress;
    /** @var  Address */
    private $deliveryAddress;
    /** @var  Card */
    private $card;
    private $saveCard = false;

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return mixed
     */
    public function getUserIp()
    {
        return $this->userIp;
    }

    /**
     * @param mixed $userIp
     */
    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;
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

    /**
     * @return mixed
     */
    public function getInstallment()
    {
        return $this->installment;
    }

    /**
     * @param mixed $installment
     */
    public function setInstallment($installment)
    {
        $this->installment = $installment;
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
     * @return Address
     */
    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     */
    public function setBillingAddress(Address $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return Address
     */
    public function getDeliveryAddress(): Address
    {
        return $this->deliveryAddress;
    }

    /**
     * @param Address $deliveryAddress
     */
    public function setDeliveryAddress(Address $deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    /**
     * @return Card
     */
    public function getCard(): Card
    {
        return $this->card;
    }

    /**
     * @param Card $card
     */
    public function setCard(Card $card)
    {
        $this->card = $card;
    }

    /**
     * @return bool
     */
    public function isSaveCard(): bool
    {
        return $this->saveCard;
    }

    /**
     * @param bool $saveCard
     */
    public function setSaveCard(bool $saveCard)
    {
        $this->saveCard = $saveCard;
    }

    /**
     * @param Setting $setting
     * @param bool $maskCardData
     * @return Request
     */
    public function getRequest(Setting $setting, bool $maskCardData = false)
    {
        $this->validate();

        $merchantConfig = Helper::getMerchantConfigFromSetting($setting);

        $user = new User($this->getUserIp());

        $product = new Product();

        $product->withCode($setting->getDefaultProductCode())
            ->withName($setting->getDefaultProductName())
            ->withPrice($this->getAmount())
            ->withQuantity(1);

        $order = new Order();

        $order->withBackRef($setting->getThreeDReturnUrl())
            ->withOrderRef($this->getOrderId())
            ->withCurrency($this->getCurrency()->getAlpha3())
            ->withOrderDate(gmdate('Y-m-d H:i:s'))
            ->withPayMethod(PayMethod::CCVISAMC)
            ->withInstallmentsNumber($this->getInstallment())
            ->addProduct($product);

        /**
         * Create new billing address
         */
        $billing = new Billing();

        /**
         * Setup the billing address params
         *
         * Full params available in the documentation
         */
        $billing->withAddressLine1($this->getBillingAddress()->getAddressLine1())
            ->withAddressLine2($this->getBillingAddress()->getAddressLine2())
            ->withCountryCode($this->getBillingAddress()->getCountryCode())
            ->withEmail($this->getBillingAddress()->getEmail())
            ->withFirstName($this->getBillingAddress()->getFirstName())
            ->withLastName($this->getBillingAddress()->getLastName())
            ->withPhoneNumber($this->getBillingAddress()->getPhoneNumber());

        /**
         * Create new delivery address
         */
        $delivery = new Delivery();

        /**
         * Setup the delivery address params
         *
         * Full params available in the documentation
         */
        $delivery->withAddressLine1($this->getBillingAddress()->getAddressLine1())
            ->withAddressLine2($this->getBillingAddress()->getAddressLine2())
            ->withCountryCode($this->getBillingAddress()->getCountryCode())
            ->withEmail($this->getBillingAddress()->getEmail())
            ->withFirstName($this->getBillingAddress()->getFirstName())
            ->withLastName($this->getBillingAddress()->getLastName())
            ->withPhoneNumber($this->getBillingAddress()->getPhoneNumber());

        $request = new Request($merchantConfig, $order, $billing, $delivery, $user);


        if (empty($this->getCard()->getCardToken())) {
            /*
             * Pay With Card
             */
            $card = new \PayU\Alu\Card(
                $this->getCard()->getCreditCardNumber($maskCardData),
                $this->getCard()->getExpiryMonth($maskCardData),
                $this->getCard()->getExpiryFullYear($maskCardData),
                $this->getCard()->getCvv($maskCardData),
                $this->getCard()->getFullName()
            );

            if ($this->isSaveCard()) {
                $card->enableTokenCreation();
            }

            $request->setCard($card);
        } else {
            if (empty($this->getCard()->getCvv())) {
                /*
                 * Pay With Saved Card Token
                 */
                $request->setCardToken(new CardToken($this->getCard()->getCardToken()));
            } else {
                /*
                 * Pay With Saved Card Token And Cvv Authorization
                 */
                $request->setCardToken(new CardToken($this->getCard()->getCardToken(), $this->getCard()->getCvv()));
            }

        }


        return $request;
    }

    public function validate()
    {
        Validator::validateNotEmpty('orderId', $this->getOrderId());
        Validator::validateIp($this->getUserIp());
        Validator::validateAmount($this->getAmount());
        Validator::validateInstallment($this->getInstallment());
        Validator::validateNotEmpty('billingAddress', $this->getBillingAddress());
        $this->getBillingAddress()->validate();
        Validator::validateNotEmpty('deliveryAddress', $this->getDeliveryAddress());
        $this->getDeliveryAddress()->validate();
        Validator::validateNotEmpty('card', $this->getCard());
        $this->getCard()->validate();
        Validator::validateCurrency($this->getCurrency());
    }
}