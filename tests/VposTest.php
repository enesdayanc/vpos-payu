<?php
/**
 * Created by PhpStorm.
 * User: enesdayanc
 * Date: 24/08/2017
 * Time: 14:50
 */

namespace PaymentGateway\VPosPayU;

use PaymentGateway\ISO4217\ISO4217;
use PaymentGateway\ISO4217\Model\Currency;
use PaymentGateway\VPosPayU\Constant\Platform;
use PaymentGateway\VPosPayU\Model\Address;
use PaymentGateway\VPosPayU\Model\Card;
use PaymentGateway\VPosPayU\Request\PurchaseRequest;
use PaymentGateway\VPosPayU\Request\RefundRequest;
use PaymentGateway\VPosPayU\Response\Response;
use PaymentGateway\VPosPayU\Setting\Credential;
use PaymentGateway\VPosPayU\Setting\Setting;
use PHPUnit\Framework\TestCase;

class VposTest extends TestCase
{
    /** @var  VPos $vPos */
    protected $vPos;
    /** @var  VPos $vPos */
    protected $vPosThreeD;
    /** @var  Card $card */
    protected $card;
    /** @var  Address */
    protected $billingAddress;
    /** @var  Address */
    protected $deliveryAddress;

    /** @var  Currency $currency */
    protected $currency;

    protected $amount;
    protected $orderId;
    protected $userIp;
    protected $installment;

    public function setUp()
    {
        $credential = new Credential();
        $credential->setMerchantCode('OPU_TEST');
        $credential->setSecretKey('SECRET_KEY');
        $credential->setPlatform(Platform::TR);

        $settings = new Setting();
        $settings->setCredential($credential);
        $settings->setThreeDReturnUrl('http://enesdayanc.com');
        $settings->setDefaultProductCode('DefaultProductCode');
        $settings->setDefaultProductName('DefaultProductName');

        $this->vPos = new VPos($settings);

        $credential = new Credential();
        $credential->setMerchantCode('PALJZXGV');
        $credential->setSecretKey('f*%J7z6_#|5]s7V4[g3]');
        $credential->setPlatform(Platform::TR);

        $settings = new Setting();
        $settings->setCredential($credential);
        $settings->setThreeDReturnUrl('http://enesdayanc.com');
        $settings->setDefaultProductCode('DefaultProductCode');
        $settings->setDefaultProductName('DefaultProductName');

        $this->vPosThreeD = new VPos($settings);

        $card = new Card();
        $card->setCreditCardNumber("4355084355084358");
        $card->setExpiryMonth('12');
        $card->setExpiryYear('18');
        $card->setFirstName('Enes');
        $card->setLastName('Dayanç');
        $card->setCvv('000');

        $this->card = $card;


        $address = new Address();
        $address->setAddressLine1('line 1');
        $address->setAddressLine2('line 2');
        $address->setCountryCode('TR');
        $address->setEmail('enes.dayanc@modanisa.com.tr');
        $address->setFirstName('Enes');
        $address->setLastName('Dayanç');
        $address->setPhoneNumber('40123456789');

        $this->billingAddress = $address;
        $this->deliveryAddress = $address;

        $iso4217 = new ISO4217();
        $this->currency = $iso4217->getByCode('TRY');

        $this->amount = rand(1, 100);
        $this->orderId = 'MO' . substr(md5(microtime() . rand()), 0, 10);
        $this->installment = 3;
        $this->userIp = '127.0.0.1';
    }

    public function testPurchase()
    {
        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserIp($this->userIp);
        $purchaseRequest->setCard($this->card);
        $purchaseRequest->setBillingAddress($this->billingAddress);
        $purchaseRequest->setDeliveryAddress($this->deliveryAddress);

        $response = $this->vPos->purchase($purchaseRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        return array(
            'amount' => $this->amount,
            'transactionReference' => $response->getTransactionReference(),
            'currency' => $this->currency,
        );
    }

    public function testPurchase3DAccount()
    {
        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserIp($this->userIp);
        $purchaseRequest->setCard($this->card);
        $purchaseRequest->setBillingAddress($this->billingAddress);
        $purchaseRequest->setDeliveryAddress($this->deliveryAddress);

        $response = $this->vPosThreeD->purchase($purchaseRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
    }

    public function testPurchaseSaveCard()
    {
        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserIp($this->userIp);
        $purchaseRequest->setCard($this->card);
        $purchaseRequest->setBillingAddress($this->billingAddress);
        $purchaseRequest->setDeliveryAddress($this->deliveryAddress);
        $purchaseRequest->setSaveCard(true);

        $response = $this->vPos->purchase($purchaseRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNotEmpty($response->getCardToken());


        // add wait for prepare payu server store card
        sleep(3);

        return array(
            'cardToken' => $response->getCardToken(),
            'cvv' => $this->card->getCvv(),
        );
    }


    /**
     * @depends testPurchaseSaveCard
     * @param $params
     */
    public function testPurchaseWithSavedCardOnlyToken($params)
    {
        $card = new Card();

        $card->setCardToken($params['cardToken']);

        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserIp($this->userIp);
        $purchaseRequest->setBillingAddress($this->billingAddress);
        $purchaseRequest->setDeliveryAddress($this->deliveryAddress);
        $purchaseRequest->setCard($card);

        $response = $this->vPos->purchase($purchaseRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    /**
     * @depends testPurchaseSaveCard
     * @param $params
     */
    public function testPurchaseWithSavedCardCvvAuth($params)
    {
        $card = new Card();

        $card->setCardToken($params['cardToken']);
        $card->setCvv($params['cvv']);

        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserIp($this->userIp);
        $purchaseRequest->setBillingAddress($this->billingAddress);
        $purchaseRequest->setDeliveryAddress($this->deliveryAddress);
        $purchaseRequest->setCard($card);

        $response = $this->vPos->purchase($purchaseRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }


    /**
     * @depends testPurchaseSaveCard
     * @param $params
     */
    public function testPurchaseFailWithSavedCardWrongVPos($params)
    {
        $card = new Card();

        $card->setCardToken($params['cardToken']);

        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->setOrderId($this->orderId);
        $purchaseRequest->setInstallment($this->installment);
        $purchaseRequest->setAmount($this->amount);
        $purchaseRequest->setCurrency($this->currency);
        $purchaseRequest->setUserIp($this->userIp);
        $purchaseRequest->setBillingAddress($this->billingAddress);
        $purchaseRequest->setDeliveryAddress($this->deliveryAddress);
        $purchaseRequest->setCard($card);

        $response = $this->vPosThreeD->purchase($purchaseRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('INVALID_CC_TOKEN', $response->getErrorCode());
    }


    /**
     * @depends testPurchase
     * @param $params
     */
    public function testRefund($params)
    {
        $refundRequest = new RefundRequest();

        $refundRequest->setOrderTotalAmount($params['amount']);
        $refundRequest->setAmount($params['amount'] / 2);
        $refundRequest->setCurrency($params['currency']);
        $refundRequest->setTransactionReference($params['transactionReference']);

        $response = $this->vPos->refund($refundRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    /**
     * @depends testPurchase
     * @param $params
     */
    public function testRefundMultipleFail($params)
    {
        $refundRequest = new RefundRequest();

        $refundRequest->setOrderTotalAmount($params['amount'] + 1);
        $refundRequest->setAmount($params['amount'] / 2);
        $refundRequest->setCurrency($params['currency']);
        $refundRequest->setTransactionReference($params['transactionReference']);

        $response = $this->vPos->refund($refundRequest);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('32', $response->getErrorCode());
    }
}