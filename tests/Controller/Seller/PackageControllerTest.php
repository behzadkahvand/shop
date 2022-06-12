<?php

namespace App\Tests\Controller\Seller;

use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageType;
use App\Entity\Seller;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Service\ORM\QueryBuilderFilterService;
use App\Tests\Controller\BaseControllerTestCase;

/**
 * Class PackageControllerTest
 */
final class PackageControllerTest extends BaseControllerTestCase
{
    public function testIndex(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest('GET', '/seller/packages');

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $packages = $response['results'];
        self::assertIsArray($packages);
        self::assertNotEmpty($packages);

        $package = current($packages);
        self::assertArrayHasKey('id', $package);
        self::assertArrayHasKey('status', $package);
        self::assertArrayHasKey('sentAt', $package);
        self::assertArrayHasKey('createdAt', $package);
        self::assertArrayHasKey('itemsTotalQuantity', $package);
    }

    public function testShow(): void
    {
        $package = $this->manager->getRepository(SellerPackage::class)->findOneBy(['seller' => $this->seller]);
        $client  = $this->loginAs($this->seller)
                        ->sendRequest('GET', "/seller/packages/{$package->getId()}");

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
        self::assertArrayHasKey('description', $package);
        self::assertArrayHasKey('items', $package);

        self::assertIsArray($package['items']);
        self::assertNotEmpty($package['items']);
        foreach ($package['items'] as $packageItem) {
            self::assertArrayHasKey('id', $packageItem);
            self::assertArrayHasKey('quantity', $packageItem);
            self::assertArrayHasKey('orderItems', $packageItem);

            self::assertIsArray($packageItem['orderItems']);
            self::assertNotEmpty($packageItem['orderItems']);
            foreach ($packageItem['orderItems'] as $orderItem) {
                self::assertArrayHasKey('status', $orderItem);
                self::assertArrayHasKey('orderItem', $orderItem);
                self::assertArrayHasKey('description', $orderItem);
                self::assertArrayHasKey('grandTotal', $orderItem['orderItem']);
                self::assertArrayHasKey('quantity', $orderItem['orderItem']);
                self::assertArrayHasKey('order', $orderItem['orderItem']);
                self::assertArrayHasKey('id', $orderItem['orderItem']['order']);
                self::assertArrayHasKey('inventory', $orderItem['orderItem']);
                self::assertArrayHasKey('sellerCode', $orderItem['orderItem']['inventory']);
            }
        }
    }

    public function testNotFoundForPackagesDoesNotBelongToSeller(): void
    {
        $anotherSeller = $this->manager->getRepository(Seller::class)->findOneBy([], ['id' => 'DESC']);
        $package       = $this->manager->getRepository(SellerPackage::class)
                                       ->findOneBy(['seller' => $this->seller], ['id' => 'DESC']);

        $package->setSeller($anotherSeller);

        $this->manager->flush();
        $this->manager->clear();

        $client = $this->loginAs($this->seller)
                       ->sendRequest('GET', "/seller/packages/{$package->getId()}");

        self::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testFailToStore(): void
    {
        $client = $this->loginAs($this->seller)->sendRequest('POST', '/seller/packages', [
            'items' => [],
        ]);

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);
        self::assertArrayHasKey('items', $response['results']);
        self::assertContains('This collection should contain 1 element or more.', $response['results']['items']);
    }

    public function testStore(): void
    {
        $sellerOrderItems = $this->getService(QueryBuilderFilterService::class)
                                 ->filter(SellerOrderItem::class, [
                                     'filter' => [
                                         'seller.id'              => $this->seller->getId(),
                                         'status'                 => SellerOrderItemStatus::WAITING_FOR_SEND,
                                         'orderItem.order.status' => OrderStatus::CONFIRMED,
                                     ],
                                 ])
                                 ->getQuery()
                                 ->getResult();

        $client = $this->loginAs($this->seller)->sendRequest('POST', '/seller/packages', [
            'items' => array_map(fn($soi) => $soi->getId(), $sellerOrderItems),
            'type' => SellerPackageType::NON_FMCG
        ]);

        self::assertEquals(201, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();
        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        $package = $response['results'];
        self::assertArrayHasKey('id', $package);
        self::assertArrayHasKey('status', $package);
        self::assertArrayHasKey('sentAt', $package);
        self::assertArrayHasKey('description', $package);

        self::assertArrayHasKey('items', $package);
        self::assertNotEmpty($package['items']);
        foreach ($package['items'] as $packageItem) {
            self::assertArrayHasKey('id', $packageItem);
            self::assertArrayHasKey('quantity', $packageItem);

            self::assertArrayHasKey('orderItems', $packageItem);
            self::assertNotEmpty($packageItem['orderItems']);
            foreach ($packageItem['orderItems'] as $orderItem) {
                self::assertArrayHasKey('status', $orderItem);
                self::assertArrayHasKey('description', $orderItem);
                self::assertArrayHasKey('orderItem', $orderItem);
                self::assertArrayHasKey('grandTotal', $orderItem['orderItem']);
                self::assertArrayHasKey('quantity', $orderItem['orderItem']);
                self::assertArrayHasKey('order', $orderItem['orderItem']);
                self::assertArrayHasKey('id', $orderItem['orderItem']['order']);
            }
        }
    }
}
