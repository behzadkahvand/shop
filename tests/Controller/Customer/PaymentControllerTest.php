<?php

namespace App\Tests\Controller\Customer;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\TransactionStatus;
use App\Entity\Configuration;
use App\Entity\Order;
use App\Entity\OrderAddress;
use App\Entity\OrderDocument;
use App\Entity\OrderShipment;
use App\Entity\Transaction;
use App\Exceptions\Order\InvalidOrderStatusException;
use App\Service\OrderShipment\OrderShipmentStatus\Events\OrderShipmentStatusChanged;
use App\Service\Payment\Gateways\CPGGateway;
use App\Service\Payment\Gateways\EFardaGateway;
use App\Service\Payment\Gateways\HamrahCardGateway;
use App\Service\Payment\Gateways\IranKishGateway;
use App\Service\Payment\Gateways\MellatGateway;
use App\Service\Payment\Gateways\ParsianGateway;
use App\Service\Payment\Gateways\SadadGateway;
use App\Service\Payment\Gateways\SamanGateway;
use App\Service\Payment\Gateways\VandarGateway;
use App\Service\Payment\Gateways\ZibalGateway;
use App\Tests\Controller\BaseControllerTestCase;
use DateTime;
use Doctrine\Persistence\ObjectRepository;
use Psr\Cache\CacheItemPoolInterface;

class PaymentControllerTest extends BaseControllerTestCase
{
    private ObjectRepository $repository;

    protected ?CacheItemPoolInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->manager->getRepository(Order::class);

        $this->cache = $this->getService(CacheItemPoolInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->repository);

        $this->cache = null;

        parent::tearDown();
    }

    /**
     * @param $defaultGatewayName
     *
     * @dataProvider defaultGatewayProvider
     */
    public function testPurchaseSuccess($defaultGatewayName)
    {
        $this->setDefaultGateway($defaultGatewayName);
        $order = $this->repository->findOneBy(
            [
                'paymentMethod' => OrderPaymentMethod::ONLINE,
                'status'        => 'WAITING_FOR_PAY',
            ]
        );

        $url = $this->getPurchaseUrl($order);

        $client = $this->sendRequest('POST', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @param $defaultGatewayName
     *
     * @dataProvider defaultGatewayProvider
     */
    public function testPurchaseRequestFail($defaultGatewayName)
    {
        $this->setDefaultGateway($defaultGatewayName);
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'paymentMethod' => OrderPaymentMethod::ONLINE,
                'status'        => 'WAITING_FOR_PAY',
            ]
        );

        $order->getOrderDocument()->setAmount(1234567890);
        $this->manager->flush();

        $url = $this->getPurchaseUrl($order);

        $client = $this->sendRequest('POST', $url);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * @param $gatewayName
     * @param $data
     *
     * @dataProvider verifyDataProvider
     */
    public function testVerifySuccess($gatewayName, $data)
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );

        /** @var Transaction $transaction */
        $transaction = $order->getOrderDocument()->getTransactions()->filter(
            fn(Transaction $transaction) => $transaction->getGateway() == $gatewayName
        )->first();

        $transaction->setToken($data['token'] ?? $data['Token']);
        $this->manager->flush();

        $url = $this->getVerifyUrl($transaction);

        $client = $this->sendRequest(
            'POST',
            $url,
            $data
        );

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('order/payment/succeed', $client->followRedirect()->getUri());
    }

    /**
     * @param $gatewayName
     * @param $data
     *
     * @dataProvider verifyDataProvider
     */
    public function testVerifyRequestFail($gatewayName, $data)
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );

        /** @var Transaction $transaction */
        $transaction = $order->getOrderDocument()->getTransactions()->filter(
            fn(Transaction $transaction) => $transaction->getGateway() == $gatewayName
        )->first();

        $transaction->setAmount(1234567890);
        $this->manager->flush();

        $url = $this->getVerifyUrl($transaction);

        $client = $this->sendRequest(
            'POST',
            $url,
            $data
        );

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('order/payment/failed', $client->followRedirect()->getUri());
    }

    public function testPurchaseFailWhenOrderIsPaid()
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'paymentMethod' => OrderPaymentMethod::ONLINE,
            ]
        );
        $order->setPaidAt(new DateTime());
        $this->manager->persist($order);
        $this->manager->flush();

        $url = $this->getPurchaseUrl($order);

        $client = $this->sendRequest('POST', $url);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testPurchaseFailWhenOrderIsNotPayable()
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'paymentMethod' => OrderPaymentMethod::ONLINE,
                'status'        => 'WAITING_FOR_PAY',
            ]
        );
        $order->getOrderDocument()->setAmount(0);
        $this->manager->persist($order);
        $this->manager->flush();

        $url = $this->getPurchaseUrl($order);

        $client = $this->sendRequest('POST', $url);

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testPurchaseHamrahCardSuccess(): void
    {
        $this->setDefaultGateway(HamrahCardGateway::getName());
        $order = $this->repository->findOneBy(
            [
                'paymentMethod' => OrderPaymentMethod::ONLINE,
                'status'        => 'WAITING_FOR_PAY',
            ]
        );

        $url = $this->getPurchaseUrl($order);

        $client = $this->sendRequest('POST', $url);

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);
        self::assertEquals([], $response['metas']);

        $results = $response['results'];

        self::assertArrayHasKey('method', $results);
        self::assertArrayHasKey('url', $results);
        self::assertArrayHasKey('inputs', $results);

        $inputs = $results['inputs'];

        self::assertArrayHasKey('qrCodeValue', $inputs);
        self::assertArrayHasKey('deepLink', $inputs);
        self::assertArrayHasKey('doTime', $inputs);
    }

    public function testPurchaseHamrahCardFail(): void
    {
        $this->setDefaultGateway(HamrahCardGateway::getName());
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'paymentMethod' => OrderPaymentMethod::ONLINE,
                'status'        => 'WAITING_FOR_PAY',
            ]
        );
        $order->getOrderDocument()->setAmount(1234567890);
        $this->manager->persist($order);
        $this->manager->flush();

        $url = $this->getPurchaseUrl($order);

        $client = $this->sendRequest('POST', $url);

        self::assertEquals(400, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertFalse($response['succeed']);
        self::assertEquals('purchase failed', $response['message']);
        self::assertEquals([], $response['results']);
        self::assertEquals([], $response['metas']);
    }

    public function testPurchaseFailWithDefaultGatewayIsNotDefined()
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'paymentMethod' => OrderPaymentMethod::ONLINE,
                'status'        => 'WAITING_FOR_PAY',
            ]
        );

        $url = $this->getPurchaseUrl($order);

        $client = $this->sendRequest('POST', $url);

        $this->assertEquals(500, $client->getResponse()->getStatusCode());
    }

    public function testVerifiedTransactionFail()
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );

        $transaction = $order->getOrderDocument()->getTransactions()->filter(
            function (Transaction $transaction) {
                return $transaction->getGateway() == IranKishGateway::NAME;
            }
        )->first();

        $transaction->setStatus(TransactionStatus::SUCCESS);

        $this->manager->flush();
        $this->manager->clear();

        $url = $this->getVerifyUrl($transaction);

        $client = $this->sendRequest(
            'POST',
            $url,
            [
                'resultCode'  => 100,
                'token'       => 123456,
                'referenceId' => 123456,
            ]
        );

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('order/payment/failed', $client->followRedirect()->getUri());
    }

    public function testVerifySadadSuccessWithAutoConfirmOrder(): void
    {
        $token    = md5(microtime(true));

        /** @var Order $order */
        $order = $this->repository->findOneBy(['identifier' => 11111111]);

        $this->createDeliveredOrder($order->getOrderAddress());

        /** @var Transaction $transaction */
        $transaction = $order->getOrderDocument()->getTransactions()->filter(function (Transaction $transaction) {
            return SadadGateway::NAME === $transaction->getGateway();
        })->first();

        $transaction->setToken($token);
        $this->manager->flush();

        $url = $this->getVerifyUrl($transaction);

        $client = $this->sendRequest('POST', $url, [
            'ResCode' => 0,
            'token'   => $token,
        ]);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('order/payment/succeed', $client->followRedirect()->getUri());
    }

    public function testVerifyMellatFailWithBankResponse()
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );

        $order->getOrderDocument()->setAmount(0);
        $this->manager->persist($order);
        $this->manager->flush();
        $transactions = $order->getOrderDocument()->getTransactions();

        $transaction = $transactions->filter(
            function (Transaction $transaction) {
                return $transaction->getGateway() == MellatGateway::NAME;
            }
        )->first();

        $url    = $this->getVerifyUrl($transaction);
        $client = $this->sendRequest(
            'POST',
            $url,
            [
                'ResCode'             => 1,
                '‫‪RefId‬‬'           => $order->getIdentifier(),
                '‫‪saleOrderId‬‬'     => $order->getIdentifier(),
                '‫‪SaleReferenceId‬‬' => '',
            ]
        );

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('order/payment/failed', $client->followRedirect()->getUri());
    }

    public function testVerifyMellatFailWithTransactionRequest()
    {
        /** @var Order $order */
        $order        = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );
        $transactions = $order->getOrderDocument()->getTransactions();

        $transaction = $transactions->filter(
            function (Transaction $transaction) {
                return $transaction->getGateway() == MellatGateway::NAME;
            }
        )->first();

        $url    = $this->getVerifyUrl($transaction);
        $client = $this->sendRequest(
            'POST',
            $url,
            [
                'ResCode'             => 0,
                '‫‪RefId‬‬'           => $order->getIdentifier(),
                '‫‪saleOrderId‬‬'     => $order->getIdentifier(),
                '‫‪SaleReferenceId‬‬' => '',
            ],
        );

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('order/payment/failed', $client->followRedirect()->getUri());
    }

    public function testVerifyHamrahCardSuccess(): void
    {
        $token    = 'sldjfljsfd';

        /** @var Order $order */
        $order        = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );
        $transactions = $order->getOrderDocument()->getTransactions();
        /** @var Transaction $transaction */
        $transaction = $transactions->filter(
            function (Transaction $transaction) {
                return $transaction->getGateway() == HamrahCardGateway::NAME;
            }
        )->first();

        $transaction->setToken($token);
        $this->manager->flush();

        $url = $this->getVerifyUrl($transaction);

        $client = $this->sendRequest(
            'POST',
            $url,
            [
                '‫‪result‬‬' => 0,
                'token'      => $token,
            ]
        );

        self::assertEquals(302, $client->getResponse()->getStatusCode());
        self::assertStringContainsString('order/payment/succeed', $client->followRedirect()->getUri());
    }

    public function testVerifyHamrahCardSuccessWithAutoConfirmOrder(): void
    {
        $token = 'sldjfljsfd';

        /** @var Order $order */
        $order = $this->repository->findOneBy(['identifier' => 11111111]);

        $this->createDeliveredOrder($order->getOrderAddress());

        /** @var Transaction $transaction */
        $transaction = $order->getOrderDocument()->getTransactions()->filter(function (Transaction $transaction) {
            return HamrahCardGateway::NAME === $transaction->getGateway();
        })->first();

        $transaction->setToken($token);
        $this->manager->flush();

        $url = $this->getVerifyUrl($transaction);

        $client = $this->sendRequest('POST', $url, [
            '‫‪result‬‬' => 0,
            'token'      => $token,
        ]);

        self::assertEquals(302, $client->getResponse()->getStatusCode());
        self::assertStringContainsString('order/payment/succeed', $client->followRedirect()->getUri());
    }

    public function testVerifyZibalCODSuccess(): void
    {
        /** @var OrderShipment $orderShipment */
        $orderShipment = $this->manager->getRepository(OrderShipment::class)->findOneBy([]);
        $orderShipment->setTransaction(null);
        $order = $orderShipment->getOrder();
        $order->setPaymentMethod(OrderPaymentMethod::OFFLINE);

        self::assertNull($orderShipment->getTransaction());

        $this->dispatcher->dispatch(
            new OrderShipmentStatusChanged($orderShipment, $orderShipment->getStatus(), OrderShipmentStatus::SENT)
        );

        $transaction = $orderShipment->getTransaction();
        $url         = $this->getCODVerifyUrl($transaction);

        $this->postJson($url, [
            'zibalId'   => 727, // @see MockedZibalPurchaseRequest
            'orderId'   => 'XXXXX',
            'refNumber' => 'REF-NO',
            'payNumber' => 'PAY-NO',
            'paidAt'    => '2021-05-22',
        ]);

        self::assertResponseIsSuccessful();

        $result = $this->getControllerResponse();

        self::assertEquals(['success' => true], $result);
    }

    /**
     * @throws InvalidOrderStatusException
     */
    public function testItCanValidateFailedPaymentsSuccessfully(): void
    {
        $this->client->disableReboot();

        /** @var Order $order */
        $order = $this->repository->findOneBy(['identifier' => 11111111]);
        $order->setCustomer($this->customer)->setStatus(OrderStatus::WAITING_FOR_PAY)->setPaidAt(null);
        $this->manager->flush();

        $response = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.payments.validate-failed', ['identifier' => $order->getIdentifier()])
        );

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        $results = $this->getControllerResponse()['results'];

        self::assertEquals(11111111, $results['identifier']);
    }

    /**
     * @throws InvalidOrderStatusException
     */
    public function testItCanValidateSucceedPaymentsSuccessfully(): void
    {
        $this->client->disableReboot();

        /** @var Order $order */
        $order = $this->repository->findOneBy(['identifier' => 11111111]);
        $order->setCustomer($this->customer)->setStatus(OrderStatus::NEW)->setPaidAt(new DateTime('now'));
        $this->manager->flush();

        $response = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.payments.validate-succeed', ['identifier' => $order->getIdentifier()])
        );

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        $results = $this->getControllerResponse()['results'];

        self::assertEquals(11111111, $results['identifier']);
    }

    /**
     * @throws InvalidOrderStatusException
     */
    public function testItFailsOnValidateFailedPaymentsIfOrderStatusIsNotWaitingForPayment(): void
    {
        $this->client->disableReboot();

        /** @var Order $order */
        $order = $this->repository->findOneBy(['identifier' => 11111111]);
        $order->setCustomer($this->customer)->setStatus(OrderStatus::CANCELED)->setPaidAt(null);
        $this->manager->flush();

        $response = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.payments.validate-failed', ['identifier' => $order->getIdentifier()])
        );

        self::assertEquals(404, $response->getResponse()->getStatusCode());
    }

    /**
     * @throws InvalidOrderStatusException
     */
    public function testItFailsOnValidateSucceedPaymentsIfOrderStatusIsCanceled(): void
    {
        $this->client->disableReboot();

        /** @var Order $order */
        $order = $this->repository->findOneBy(['identifier' => 11111111]);
        $order->setCustomer($this->customer)->setStatus(OrderStatus::CANCELED)->setPaidAt(new DateTime('now'));
        $this->manager->flush();

        $response = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.payments.validate-succeed', ['identifier' => $order->getIdentifier()])
        );

        self::assertEquals(404, $response->getResponse()->getStatusCode());
    }

    /**
     * @throws InvalidOrderStatusException
     */
    public function testItFailsOnValidateFailedPaymentsIfOrderNotBelongsToUser(): void
    {
        $this->client->disableReboot();

        /** @var Order $order */
        $order = $this->repository->findOneBy(['identifier' => 11111111]);
        $order->setStatus(OrderStatus::WAITING_FOR_PAY)->setPaidAt(null);
        $this->manager->flush();

        $response = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.payments.validate-failed', ['identifier' => $order->getIdentifier()])
        );

        self::assertEquals(404, $response->getResponse()->getStatusCode());
    }

    /**
     * @throws InvalidOrderStatusException
     */
    public function testItFailsOnValidateSucceedPaymentsIfOrderNotBelongsToUser(): void
    {
        $this->client->disableReboot();

        /** @var Order $order */
        $order = $this->repository->findOneBy(['identifier' => 11111111]);
        $order->setStatus(OrderStatus::WAITING_FOR_PAY)->setPaidAt(new DateTime('now'));
        $this->manager->flush();

        $response = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.payments.validate-succeed', ['identifier' => $order->getIdentifier()])
        );

        self::assertEquals(404, $response->getResponse()->getStatusCode());
    }

    public function testItFailsOnValidateFailedPaymentsIfOrderIsInvalid(): void
    {
        $response = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.payments.validate-failed', ['identifier' => 123456789])
        );

        self::assertEquals(404, $response->getResponse()->getStatusCode());
    }

    public function testItFailsOnValidateSucceedPaymentsIfOrderIsInvalid(): void
    {
        $response = $this->loginAs($this->customer)->sendRequest(
            'GET',
            $this->route('customer.payments.validate-succeed', ['identifier' => 123456789])
        );

        self::assertEquals(404, $response->getResponse()->getStatusCode());
    }

    public function testItCanGetHamrahCardTransactionData(): void
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );

        $transactions = $order->getOrderDocument()->getTransactions();
        /** @var Transaction $transaction */
        $transaction = $transactions->filter(
            function (Transaction $transaction) {
                return $transaction->getGateway() == HamrahCardGateway::NAME;
            }
        )->first();

        $this->setTransactionCache($transaction);

        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.payments.transaction_data',
                [
                    'identifier' => $order->getIdentifier(),
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);
        self::assertEquals([], $response['metas']);

        $results = $response['results'];

        self::assertArrayHasKey('qrCodeValue', $results);
        self::assertArrayHasKey('deeplink', $results);
        self::assertArrayHasKey('doTime', $results);
        self::assertArrayHasKey('orderIdentifier', $results);
        self::assertArrayHasKey('orderCreatedAt', $results);
        self::assertArrayHasKey('transactionIdentifier', $results);
        self::assertArrayHasKey('transactionAmount', $results);

        self::assertEquals('QR code value', $results['qrCodeValue']);
        self::assertEquals('‫‪https://qrpayment.efarda.ir', $results['deeplink']);
        self::assertEquals('1399/12/18', $results['doTime']);
        self::assertEquals($order->getIdentifier(), $results['orderIdentifier']);
        self::assertEquals($transaction->getIdentifier(), $results['transactionIdentifier']);
        self::assertEquals($transaction->getAmount(), $results['transactionAmount']);
    }

    public function testValidationFailureOnGetTransactionDataWhenTransactionNotFound(): void
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy([]);

        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.payments.transaction_data',
                [
                    'identifier' => $order->getIdentifier(),
                ]
            )
        );

        self::assertEquals(404, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertFalse($response['succeed']);
        self::assertEquals('Transaction not found!', $response['message']);
        self::assertEquals([], $response['results']);
        self::assertEquals([], $response['metas']);
    }

    public function testValidationFailureOnGetTransactionDataWhenTransactionIsExpired(): void
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );

        $transactions = $order->getOrderDocument()->getTransactions();
        /** @var Transaction $transaction */
        $transaction = $transactions->filter(
            function (Transaction $transaction) {
                return $transaction->getGateway() == HamrahCardGateway::NAME;
            }
        )->first();

        $this->unsetTransactionCache($transaction);

        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.payments.transaction_data',
                [
                    'identifier' => $order->getIdentifier(),
                ]
            )
        );

        self::assertEquals(404, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertFalse($response['succeed']);
        self::assertEquals('Transaction not found!', $response['message']);
        self::assertEquals([], $response['results']);
        self::assertEquals([], $response['metas']);
    }

    public function testItCanSuccessInquiryHamrahCardTransaction(): void
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );

        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.payments.transaction_inquiry',
                [
                    'identifier' => $order->getIdentifier(),
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);
        self::assertEquals([], $response['metas']);

        $results = $response['results'];

        self::assertArrayHasKey('transactionStatus', $results);

        self::assertTrue($results['transactionStatus']);
    }

    public function testItCanUnsuccessInquiryHamrahCardTransaction(): void
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy(
            [
                'identifier' => 11111111,
            ]
        );

        $transactions = $order->getOrderDocument()->getTransactions();
        /** @var Transaction $transaction */
        $transaction = $transactions->filter(
            function (Transaction $transaction) {
                return $transaction->getGateway() == HamrahCardGateway::NAME;
            }
        )->first();

        $transaction->setAmount(1234567890);
        $this->manager->flush();

        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.payments.transaction_inquiry',
                [
                    'identifier' => $order->getIdentifier(),
                ]
            )
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);
        self::assertEquals([], $response['metas']);

        $results = $response['results'];

        self::assertArrayHasKey('transactionStatus', $results);

        self::assertFalse($results['transactionStatus']);
    }

    public function testValidationFailureOnInquiryTransactionWhenTransactionNotFound(): void
    {
        /** @var Order $order */
        $order = $this->repository->findOneBy([]);

        $client = $this->sendRequest(
            'GET',
            $this->router->generate(
                'customer.payments.transaction_data',
                [
                    'identifier' => $order->getIdentifier(),
                ]
            )
        );

        self::assertEquals(404, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertFalse($response['succeed']);
        self::assertEquals('Transaction not found!', $response['message']);
        self::assertEquals([], $response['results']);
        self::assertEquals([], $response['metas']);
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    private function getPurchaseUrl(Order $order): string
    {
        return "/payment/{$order->getIdentifier()}/purchase";
    }

    /**
     * @param Transaction $transaction
     *
     * @return string
     */
    private function getVerifyUrl(Transaction $transaction): string
    {
        return "/payment/{$transaction->getIdentifier()}/verify";
    }

    private function getCODVerifyUrl(Transaction $transaction): string
    {
        return "/payment/{$transaction->getIdentifier()}/cod-verify";
    }

    private function setDefaultGateway($gatewayName): void
    {
        $configuration = new Configuration();
        $configuration->setCode(ConfigurationCodeDictionary::DEFAULT_ONLINE_GATEWAY)
                      ->setValue($gatewayName);
        $this->manager->persist($configuration);
        $this->manager->flush();
    }

    private function createDeliveredOrder(OrderAddress $orderAddress): Order
    {
        $cart = $this->customer->getCartOrFail();

        $order = (new Order())
            ->setCustomer($orderAddress->getCustomerAddress()->getCustomer())
            ->setGrandTotal($cart->getGrandTotal())
            ->setSubtotal($cart->getSubtotal())
            ->setStatus(OrderStatus::DELIVERED)
            ->setBalanceAmount(0)
            ->setPaymentMethod(OrderPaymentMethod::OFFLINE);

        $orderDocument = (new OrderDocument())
            ->setCompletedAt(new \DateTime())
            ->setAmount($order->getGrandTotal())
            ->setOrder($order);

        $order->setOrderDocument($orderDocument);

        $orderAddress2 = clone $orderAddress;
        $orderAddress2->setOrder($order);
        $order->addOrderAddress($orderAddress2);

        $this->manager->persist($order);
        $this->manager->persist($orderDocument);
        $this->manager->flush();

        return $order;
    }

    private function setTransactionCache(Transaction $transaction): void
    {
        $cacheItem = $this->cache->getItem(HamrahCardGateway::CACHE_NAME . $transaction->getIdentifier());

        $cacheItem->set([
            'qrCodeValue' => 'QR code value',
            'deeplink'    => '‫‪https://qrpayment.efarda.ir',
            'doTime'      => '1399/12/18',
        ]);

        $this->cache->save($cacheItem);
    }

    private function unsetTransactionCache(Transaction $transaction): void
    {
        $this->cache->deleteItem(HamrahCardGateway::CACHE_NAME . $transaction->getIdentifier());
    }

    public function defaultGatewayProvider()
    {
        $gateways = [
            SadadGateway::NAME,
            CPGGateway::NAME,
            IranKishGateway::NAME,
            MellatGateway::NAME,
            SamanGateway::NAME,
            ParsianGateway::NAME,
            SadadGateway::NAME,
            VandarGateway::NAME,
            ZibalGateway::NAME,
            EFardaGateway::NAME,
        ];
        foreach ($gateways as $gateway) {
            yield [$gateway];
        }
    }

    public function verifyDataProvider()
    {
        $gateways = [
            CPGGateway::NAME        => [
                'payment_status' => 'OK',
                'token'          => md5(microtime(true)),
            ],
            IranKishGateway::NAME   => [
                'resultCode'  => 100,
                'token'       => md5(microtime(true)),
                'referenceId' => md5(microtime(true)),
            ],
            MellatGateway::NAME     => [
                'ResCode'             => 0,
                '‫‪RefId‬‬'           => 'mellat',
                '‫‪saleOrderId‬‬'     => 1,
                '‫‪SaleReferenceId‬‬' => md5(microtime(true)),
                'token'               => 'mellat',
            ],
            ParsianGateway::NAME    => [
                'status' => 0,
                'Token'  => md5(microtime(true)),
                'RRN'    => md5(microtime(true)),
            ],
            SadadGateway::NAME      => [
                'ResCode' => 0,
                'token'   => md5(microtime(true)),
            ],
            VandarGateway::NAME     => [
                'payment_status' => 'DONE',
                'token'          => md5(microtime(true)),
            ],
            SamanGateway::NAME      => [
                'RefNum'  => 'saman',
                'TraceNo' => md5(microtime(true)),
                'token'   => 'saman',
            ],
            ZibalGateway::NAME      => [
                'success' => 1,
                'trackId' => 'zibal',
                'token'   => 'zibal',
            ],
            HamrahCardGateway::NAME => [
                'token' => md5(microtime(true)),
            ],
            EFardaGateway::NAME     => [
                'result'           => 0,
                'traceNumber'      => 'e-farda',
                'channelRefNumber' => md5(microtime(true)),
                'token'            => 'e-farda',
            ],
        ];
        foreach ($gateways as $gatewayName => $data) {
            yield [$gatewayName, $data];
        }
    }
}
