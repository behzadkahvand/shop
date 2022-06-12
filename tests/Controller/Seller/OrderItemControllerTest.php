<?php

namespace App\Tests\Controller\Seller;

use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerOrderItemStatusMappingDictionary;
use App\Entity\Order;
use App\Entity\Seller;
use App\Entity\SellerOrderItem;
use App\Service\ORM\QueryBuilderFilterService;
use App\Tests\Controller\BaseControllerTestCase;

class OrderItemControllerTest extends BaseControllerTestCase
{
    public function testItThrowExceptionIfOrderIsNotConfirmed()
    {
        $sellerOrderItem = $this->manager->getRepository(SellerOrderItem::class)->findOneBy([]);
        $sellerOrderItem->setStatus(SellerOrderItemStatus::CANCELED_BY_USER);

        /**
         * @var Order $order
         */
        $order = $sellerOrderItem->getOrderItem()->getOrder();
        $order->setStatus(OrderStatus::DELIVERED);

        $this->manager->flush();
        $this->manager->clear();

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            "/seller/order-items/{$sellerOrderItem->getId()}/sent",
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(422, $response['status']);
        self::assertEquals('The seller order status is invalid!', $response['detail']);
    }

    public function testItThrowAccessDeniedExceptionIfSellerOrderItemIsRejected()
    {
        $sellerOrderItem = $this->manager->getRepository(SellerOrderItem::class)->findOneBy([]);
        $sellerOrderItem->setStatus(SellerOrderItemStatus::CANCELED_BY_USER);

        /**
         * @var Order $order
         */
        $order = $sellerOrderItem->getOrderItem()->getOrder();
        $order->setStatus(OrderStatus::CONFIRMED);

        $this->manager->flush();
        $this->manager->clear();

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            "/seller/order-items/{$sellerOrderItem->getId()}/sent",
        );

        self::assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testItThrowNotFoundExceptionIfSellerOrderItemIdIsNotValid()
    {
        $sellerOrderItem = $this->manager->getRepository(SellerOrderItem::class)->findOneBy([], ['id' => 'DESC']);
        $id              = $sellerOrderItem->getId() + 1;

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            "/seller/order-items/{$id}/sent",
        );

        self::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testItThrowNotFoundExceptionIfSellerDoesNotOwnSellerOrderItem()
    {
        $sellerOrderItem = $this->manager->getRepository(SellerOrderItem::class)->findOneBy([]);
        $anotherSeller   = $this->getService(QueryBuilderFilterService::class)
                                ->filter(Seller::class, [
                                    'filter' => [
                                        'id' => ['neq' => $sellerOrderItem->getSeller()->getId()],
                                    ],
                                ])->getQuery()->getResult()[0];

        $sellerOrderItem->setSeller($anotherSeller);
        $this->manager->flush();
        $this->manager->clear();

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            "/seller/order-items/{$sellerOrderItem->getId()}/sent",
        );

        self::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testItConfirmSendingSellerOrderItem()
    {
        $sellerOrderItem = $this->manager->getRepository(SellerOrderItem::class)
                                         ->findOneBy([]);

        $sellerOrderItem->setStatus(SellerOrderItemStatus::WAITING_FOR_SEND);

        /**
         * @var Order $order
         */
        $order = $sellerOrderItem->getOrderItem()->getOrder();
        $order->setStatus(OrderStatus::CONFIRMED);

        $this->manager->flush();
        $this->manager->clear();

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            "/seller/order-items/{$sellerOrderItem->getId()}/sent",
            [
                'description' => 'IMEI: 1234567890',
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $package = $response['results'];
        self::assertArrayHasKey('id', $package);
        self::assertArrayHasKey('status', $package);
        self::assertArrayHasKey('sentAt', $package);
        self::assertArrayHasKey('items', $package);

        $packageItems = $package['items'];
        self::assertIsArray($packageItems);

        foreach ($packageItems as $packageItem) {
            self::assertArrayHasKey('id', $packageItem);
            self::assertArrayHasKey('quantity', $packageItem);
            self::assertArrayHasKey('orderItems', $packageItem);

            $orderItems = $packageItem['orderItems'];
            self::assertIsArray($orderItems);

            foreach ($orderItems as $orderItem) {
                self::assertArrayHasKey('status', $orderItem);
                self::assertArrayHasKey('orderItem', $orderItem);
                self::assertArrayHasKey('grandTotal', $orderItem['orderItem']);
                self::assertArrayHasKey('quantity', $orderItem['orderItem']);
                self::assertArrayHasKey('order', $orderItem['orderItem']);
                self::assertArrayHasKey('id', $orderItem['orderItem']['order']);
            }
        }
    }

    public function testReportWithSingleStatusFilter()
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.order_items.report', [
                'filter' => ['status' => SellerOrderItemStatusMappingDictionary::WAITING_FOR_SEND],
            ]),
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = data_get($this->getControllerResponse(), 'results');

        foreach ($response as $item) {
            self::assertEquals(SellerOrderItemStatus::WAITING_FOR_SEND, $item['status']);
            self::assertArrayHasKey('orderItem', $item);
            self::assertArrayHasKey('inventory', $item['orderItem']);
            self::assertArrayHasKey('sellerCode', $item['orderItem']['inventory']);
        }
    }

    public function testReportWithMultipleStatusFilter()
    {
        $statuses = [
            SellerOrderItemStatusMappingDictionary::WAITING_FOR_SEND,
            SellerOrderItemStatusMappingDictionary::WAITING,
        ];

        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.order_items.report', ['filter' => ['status' => ['in' => implode(',', $statuses)]]]),
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = data_get($this->getControllerResponse(), 'results');

        foreach ($response as $item) {
            self::assertContains($item['status'], $statuses);
        }
    }

    public function testReport()
    {
        $client = $this->loginAs($this->seller)->sendRequest(
            'GET',
            $this->route('seller.order_items.report'),
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $items = $response['results'];
        self::assertIsArray($items);

        $mappedStatuses = array_unique(array_values(SellerOrderItemStatusMappingDictionary::toArray()));

        foreach ($items as $item) {
            self::assertArrayHasKey('status', $item);
            self::assertArrayHasKey('orderItem', $item);
            self::assertArrayHasKey('deliveryDate', $item);
            self::assertArrayHasKey('description', $item);

            $orderItem = $item['orderItem'];
            self::assertArrayHasKey('grandTotal', $orderItem);
            self::assertArrayHasKey('quantity', $orderItem);
            self::assertArrayHasKey('inventory', $orderItem);
            $inventory = $orderItem['inventory'];

            self::assertArrayHasKey('stock', $inventory);
            self::assertArrayHasKey('variant', $inventory);
            self::assertArrayHasKey('sellerCode', $inventory);

            $variant = $inventory['variant'];
            self::assertArrayHasKey('id', $variant);
            self::assertArrayHasKey('product', $variant);

            $product = $variant['product'];
            self::assertArrayHasKey('id', $product);
            self::assertArrayHasKey('title', $product);
            self::assertArrayHasKey('category', $product);
            self::assertArrayHasKey('featuredImage', $product);

            self::assertArrayHasKey('order', $orderItem);

            $order = $orderItem['order'];

            self::assertArrayHasKey('id', $order);
            self::assertArrayHasKey('status', $order);
            self::assertArrayHasKey('createdAt', $order);
            self::assertContains($order['status'], $mappedStatuses);
        }
    }

    public function testItUpdateSellerOrderItemDescription(): void
    {
        $sellerOrderItem = $this->manager->getRepository(SellerOrderItem::class)
                                         ->findOneBy([
                                             'seller' => $this->seller,
                                         ]);

        $description = $this->faker->sentence(10);
        $client      = $this->loginAs($this->seller)
                            ->sendRequest('PATCH', "/seller/order-items/{$sellerOrderItem->getId()}", [
                                'description' => $description,
                            ]);

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $sellerOrderItem = $response['results'];

        $mappedStatuses = array_unique(array_values(SellerOrderItemStatusMappingDictionary::toArray()));

        self::assertArrayHasKey('status', $sellerOrderItem);
        self::assertArrayHasKey('orderItem', $sellerOrderItem);
        self::assertArrayHasKey('deliveryDate', $sellerOrderItem);
        self::assertArrayHasKey('description', $sellerOrderItem);
        self::assertEquals($description, $sellerOrderItem['description']);

        $orderItem = $sellerOrderItem['orderItem'];
        self::assertArrayHasKey('grandTotal', $orderItem);
        self::assertArrayHasKey('quantity', $orderItem);
        self::assertArrayHasKey('inventory', $orderItem);
        $inventory = $orderItem['inventory'];

        self::assertArrayHasKey('stock', $inventory);
        self::assertArrayHasKey('variant', $inventory);

        $variant = $inventory['variant'];
        self::assertArrayHasKey('id', $variant);
        self::assertArrayHasKey('product', $variant);

        $product = $variant['product'];
        self::assertArrayHasKey('id', $product);
        self::assertArrayHasKey('title', $product);
        self::assertArrayHasKey('category', $product);
        self::assertArrayHasKey('featuredImage', $product);

        self::assertArrayHasKey('order', $orderItem);

        $order = $orderItem['order'];

        self::assertArrayHasKey('id', $order);
        self::assertArrayHasKey('status', $order);
        self::assertArrayHasKey('createdAt', $order);
        self::assertContains($order['status'], $mappedStatuses);
    }

    public function testItThrowNotFoundExceptionIfSellerOrderItemIdDoesNotBelongToSeller(): void
    {
        $sellerOrderItem = $this->getService(QueryBuilderFilterService::class)
                                ->filter(SellerOrderItem::class, [
                                    'filter' => [
                                        'seller.id' => ['neq' => $this->seller->getId()],
                                    ],
                                ])
                                ->getQuery()
                                ->getResult()[0];

        $client = $this->loginAs($this->seller)->sendRequest(
            'PATCH',
            "/seller/order-items/{$sellerOrderItem->getId()}",
        );

        self::assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
