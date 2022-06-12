<?php

namespace App\Tests\Controller\Carrier;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\OrderPromotionDiscount;
use App\Entity\OrderShipment;
use App\Entity\Transaction;
use App\Tests\Controller\BaseControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class InquiryControllerTest extends BaseControllerTestCase
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
            ->sendRequest('GET', $this->route('carrier.inquiry.show', ['id' => $wrongShipment]));

        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getResponse()->getStatusCode());
    }

    public function testItFailsIfOrderPaymentMethodIsNotOffline(): void
    {
        $this->orderShipment->getOrder()->setPaymentMethod(OrderPaymentMethod::ONLINE);
        $this->manager->flush();

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest('GET', $this->route('carrier.inquiry.show', ['id' => $this->orderShipment->getId()]));

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getResponse()->getStatusCode());
    }

    public function testItFailsIfShipmentAlreadyHasBeenPaid(): void
    {
        $this->orderShipment->setTransaction($this->transaction);
        $this->manager->flush();

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest('GET', $this->route('carrier.inquiry.show', ['id' => $this->orderShipment->getId()]));

        self::assertEquals(Response::HTTP_CONFLICT, $response->getResponse()->getStatusCode());
    }

    public function testItCanInquireOrderShipmentInfoSuccessfully(): void
    {
        $this->orderShipment->getOrder()->setPaymentMethod(OrderPaymentMethod::OFFLINE);
        $this->orderShipment->setTransaction(null);
        $this->orderShipment->addDiscount($this->orderPromotionDiscount);
        $this->manager->flush();

        $response = $this
            ->loginAs($this->admin)
            ->sendRequest('GET', $this->route('carrier.inquiry.show', ['id' => $this->orderShipment->getId()]));

        self::assertEquals(Response::HTTP_OK, $response->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        $orderShipmentInfo = $response['results'];
        self::assertIsArray($orderShipmentInfo);
        self::assertArrayHasKey('order', $orderShipmentInfo);

        $order = $orderShipmentInfo['order'];
        self::assertIsArray($order);
        self::assertArrayHasKey('customer', $order);
        self::assertArrayHasKey('orderAddress', $order);

        $customer = $order['customer'];
        self::assertIsArray($customer);
        self::assertArrayHasKey('name', $customer);
        self::assertArrayHasKey('family', $customer);
        self::assertArrayHasKey('mobile', $customer);

        $orderAddress = $order['orderAddress'];
        self::assertIsArray($orderAddress);
        self::assertArrayHasKey('fullAddress', $orderAddress);
        self::assertArrayHasKey('unit', $orderAddress);
        self::assertArrayHasKey('floor', $orderAddress);
        self::assertArrayHasKey('name', $orderAddress);
        self::assertArrayHasKey('family', $orderAddress);
        self::assertArrayHasKey('phone', $orderAddress);
        self::assertArrayHasKey('city', $orderAddress);

        $city = $orderAddress['city'];
        self::assertIsArray($city);
        self::assertArrayHasKey('name', $city);
        self::assertArrayHasKey('province', $city);

        $province = $city['province'];
        self::assertIsArray($province);
        self::assertArrayHasKey('name', $province);

        $orderItems = $orderShipmentInfo['orderItems'];
        self::assertIsArray($orderItems);

        foreach ($orderItems as $orderItem) {
            self::assertArrayHasKey('quantity', $orderItem);
            self::assertArrayHasKey('inventory', $orderItem);

            $inventory = $orderItem['inventory'];
            self::assertIsArray($inventory);
            self::assertArrayHasKey('variant', $inventory);

            $variant = $inventory['variant'];
            self::assertIsArray($variant);
            self::assertArrayHasKey('product', $variant);
            self::assertArrayHasKey('options', $variant);

            $product = $variant['product'];
            self::assertIsArray($product);
            self::assertArrayHasKey('title', $product);

            $options = $variant['options'];
            self::assertIsArray($options);
            self::assertArrayHasKey('color', $options);
            self::assertArrayHasKey('guarantee', $options);
            self::assertArrayHasKey('otherOptions', $options);

            self::assertArrayHasKey('sellingPrice', $orderItem);
        }

        self::assertArrayHasKey('discount', $orderShipmentInfo);

        self::assertArrayHasKey('payable', $orderShipmentInfo);
    }
}
