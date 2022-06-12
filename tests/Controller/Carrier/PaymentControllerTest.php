<?php

namespace App\Tests\Controller\Carrier;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\TransactionStatus;
use App\Entity\OrderPromotionDiscount;
use App\Entity\OrderShipment;
use App\Entity\Transaction;
use App\Service\Payment\Gateways\CODGateway;
use App\Tests\Controller\BaseControllerTestCase;
use DateTime;
use Symfony\Component\HttpFoundation\Response;

class PaymentControllerTest extends BaseControllerTestCase
{
    private OrderShipment $orderShipment;

    private Transaction $transaction;

    private OrderPromotionDiscount $orderPromotionDiscount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderShipment = $this->manager->getRepository(OrderShipment::class)->findOneBy([]);
        $this->transaction = $this->manager->getRepository(Transaction::class)->findOneBy([]);
        $this->orderPromotionDiscount = $this->manager->getRepository(OrderPromotionDiscount::class)->findOneBy([]);
    }

    public function testItFailsIfOrderShipmentIsWrong(): void
    {
        $wrongShipment = 9934534;

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest('POST', $this->route('carrier.payment.purchase', ['id' => $wrongShipment]));

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getResponse()->getStatusCode());
    }

    public function testItFailsOnValidationIfRequiredFieldsAreNotPresent(): void
    {
        $response = $this
            ->loginAs($this->admin)
            ->sendRequest('POST', $this->route('carrier.payment.purchase', ['id' => $this->orderShipment->getId()]));

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getResponse()->getStatusCode());

        $result = $this->getControllerResponse()['results'];

        $keys = [
            'terminalNumber',
            'merchantId',
            'posSerial',
            'reserveNumber',
            'traceNumber',
            'rrn',
            'ref',
            'amount',
            'txnDate',
            'txnTime',
            'maskedPan',
        ];

        foreach ($keys as $key) {
            self::assertArrayHasKey($key, $result);
            self::assertEquals('This value should not be null.', $result[$key][0]);
        }
    }

    public function testItFailsWhenTransactionAmountIsNotEquivalentToShipmentPayable(): void
    {
        $wrongAmount = 999;

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest(
                'POST',
                $this->route('carrier.payment.purchase', ['id' => $this->orderShipment->getId()]),
                $this->getTransactionData($wrongAmount),
            );

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getResponse()->getStatusCode());

        $message = $this->getControllerResponse()['message'];

        self::assertEquals('The amount is not equivalent to the selected shipment payable price!', $message);
    }

    public function testItFailsWhenOrderStatusIsAnythingButConfirmed(): void
    {
        $this->orderShipment->getOrder()->setStatus(OrderStatus::CANCELED);
        $this->manager->flush();

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest(
                'POST',
                $this->route('carrier.payment.purchase', ['id' => $this->orderShipment->getId()]),
                $this->getTransactionData(),
            );

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getResponse()->getStatusCode());

        $message = $this->getControllerResponse()['message'];

        self::assertEquals('The selected order is not confirmed!', $message);
    }

    public function testItFailsWhenOrderAlreadyHasBeenFullyPaid(): void
    {
        $this->orderShipment
            ->getOrder()
            ->setStatus(OrderStatus::CONFIRMED)
            ->setPaidAt(new DateTime());
        $this->manager->flush();

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest(
                'POST',
                $this->route('carrier.payment.purchase', ['id' => $this->orderShipment->getId()]),
                $this->getTransactionData(),
            );

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getResponse()->getStatusCode());

        $message = $this->getControllerResponse()['message'];

        self::assertEquals('The selected order already has been fully paid!', $message);
    }

    public function testItFailsWhenOrderPaymentMethodIsNotOffline(): void
    {
        $this->orderShipment
            ->getOrder()
            ->setStatus(OrderStatus::CONFIRMED)
            ->setPaymentMethod(OrderPaymentMethod::ONLINE);
        $this->manager->flush();

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest(
                'POST',
                $this->route('carrier.payment.purchase', ['id' => $this->orderShipment->getId()]),
                $this->getTransactionData(),
            );

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getResponse()->getStatusCode());

        $message = $this->getControllerResponse()['message'];

        self::assertEquals('The selected order payment method should be offline!', $message);
    }

    public function testItFailsWhenShipmentAlreadyHasBeenPaid(): void
    {
        $this->orderShipment->getOrder()->setStatus(OrderStatus::CONFIRMED);
        $this->transaction->setStatus(TransactionStatus::SUCCESS);
        $this->orderShipment->setTransaction($this->transaction);
        $this->manager->flush();

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest(
                'POST',
                $this->route('carrier.payment.purchase', ['id' => $this->orderShipment->getId()]),
                $this->getTransactionData(),
            );

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getResponse()->getStatusCode());

        $message = $this->getControllerResponse()['message'];

        self::assertEquals('The selected shipment already has been paid!', $message);
    }

    public function testItFailsWhenShipmentStatusIsAnythingButSent(): void
    {
        $this->orderShipment->getOrder()->setStatus(OrderStatus::CONFIRMED);
        $this->orderShipment->setStatus(OrderShipmentStatus::PREPARING)->setTransaction(null);
        $this->manager->flush();

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest(
                'POST',
                $this->route('carrier.payment.purchase', ['id' => $this->orderShipment->getId()]),
                $this->getTransactionData(),
            );

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getResponse()->getStatusCode());

        $message = $this->getControllerResponse()['message'];

        self::assertEquals('The selected shipment status should be sent!', $message);
    }

    public function testItCanRegisterCODTransactionSuccessfully(): void
    {
        $order = $this->orderShipment->getOrder();
        $order
            ->setStatus(OrderStatus::CONFIRMED)
            ->setPaymentMethod(OrderPaymentMethod::OFFLINE);

        $this->orderShipment
            ->setTransaction(null)
            ->setStatus(OrderShipmentStatus::SENT);

        $this->manager->flush();

        $transactionData = $this->getTransactionData();
        $response = $this
            ->loginAs($this->admin)
            ->sendRequest(
                'POST',
                $this->route('carrier.payment.purchase', ['id' => $this->orderShipment->getId()]),
                $transactionData,
            );

        self::assertEquals(Response::HTTP_OK, $response->getResponse()->getStatusCode());

        $transaction = $this->orderShipment->getTransaction();
        self::assertNotNull($transaction);
        self::assertEquals(TransactionStatus::SUCCESS, $transaction->getStatus());
        self::assertEquals($transactionData['amount'] / 10, $transaction->getAmount()); // Convert Rials to Toman
        self::assertEquals(CODGateway::getName(), $transaction->getGateway());
        self::assertEquals($transactionData['rrn'], $transaction->getReferenceNumber());
        self::assertEquals($transactionData['ref'], $transaction->getTrackingNumber());
        self::assertNotNull($transaction->getPaidAt());

        self::assertEquals(OrderShipmentStatus::DELIVERED, $this->orderShipment->getStatus());

        $order = $this->orderShipment->getOrder();
        if ($order->getStatus() === OrderStatus::DELIVERED) {
            self::assertNotNull($order->getPaidAt());
        }
    }

    private function getTransactionData(?int $amount = null): array
    {
        return [
            'terminalNo' => (string) $this->faker->randomNumber(8, true),
            'merchantId' => (string) $this->faker->randomNumber(8, true),
            'posSerial' => (string) $this->faker->randomNumber(8, true),
            'reserveNumber' => (string) $this->faker->randomNumber(8, true),
            'traceNumber' => (string) $this->faker->randomNumber(8, true),
            'rrn' => (string) $this->faker->randomNumber(8, true),
            'ref' => (string) $this->faker->randomNumber(8, true),
            'amount' => (string) ($amount ?? $this->orderShipment->getPayable()),
            'txnDate' => $this->faker->date(),
            'txnTime' => $this->faker->time(),
            'maskedPan' => (string) $this->faker->randomNumber(8, true),
        ];
    }
}
