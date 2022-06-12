<?php

namespace App\Tests\Controller\Customer;

use App\Dictionary\CityDictionary;
use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Dictionary\ProductStatusDictionary;
use App\Entity\Cart;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use App\Entity\OrderAddress;
use App\Entity\OrderDocument;
use App\Entity\OrderShipment;
use App\Entity\PromotionCoupon;
use App\Entity\PromotionRule;
use App\Repository\CustomerRepository;
use App\Repository\PromotionCouponRepository;
use App\Service\Cart\CartService;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\PartialShipment\PartialShipmentService;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\ValueObject\ExpressPartialDeliveryDate;
use App\Tests\Controller\BaseControllerTestCase;
use App\Tests\Controller\Traits\PromotionTrait;
use Doctrine\ORM\QueryBuilder;

class OrderControllerTest extends BaseControllerTestCase
{
    use PromotionTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer->setName('علی');
        $this->customer->setFamily('علی نقیان');
        $this->customer->setNationalNumber('0708310656');

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $response = $this->loginAs($this->customer)
                         ->sendRequest('GET', $this->router->generate('customer.orders.index'));

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        $result = $this->getControllerResponse();

        self::assertIsArray($result['results']);

        $result = $result['results'][0];

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('status', $result);
        self::assertArrayHasKey('identifier', $result);
        self::assertArrayHasKey('subtotal', $result);
        self::assertArrayHasKey('grandTotal', $result);
        self::assertArrayHasKey('payable', $result);
        self::assertArrayHasKey('createdAt', $result);
        self::assertArrayHasKey('isPaid', $result);
        self::assertTrue($result['promotionLocked']);
    }

    public function testIndexWithStatusFilter(): void
    {
        $response = $this->loginAs($this->customer)
                         ->sendRequest('GET', $this->router->generate('customer.orders.index', [
                             'filter' => [
                                 'status' => 'CURRENT',
                             ],
                         ]));

        self::assertEquals(200, $response->getResponse()->getStatusCode());

        $result = $this->getControllerResponse();

        self::assertIsArray($result['results']);

        $result = $result['results'][0];

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('status', $result);
        self::assertArrayHasKey('identifier', $result);
        self::assertArrayHasKey('subtotal', $result);
        self::assertArrayHasKey('grandTotal', $result);
        self::assertArrayHasKey('payable', $result);
        self::assertArrayHasKey('createdAt', $result);
        self::assertArrayHasKey('isPaid', $result);
        self::assertTrue($result['promotionLocked']);
    }

    public function testIndexWithInvalidStatusFilter(): void
    {
        $client = $this->loginAs($this->customer)
                       ->sendRequest('GET', $this->router->generate('customer.orders.index', [
                           'filter' => [
                               'status' => 'INVALID',
                           ],
                       ]));

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('title', $response);
        self::assertArrayHasKey('status', $response);
        self::assertArrayHasKey('detail', $response);

        self::assertEquals('An error occurred', $response['title']);
        self::assertEquals(422, $response['status']);
        self::assertEquals('Order status is invalid!', $response['detail']);
    }

    public function testShow(): void
    {
        $orderShipment = $this->manager->getRepository(OrderShipment::class)->findOneBy([]);
        $order         = $this->manager->getRepository(Order::class)->findOneBy([]);

        $order->addShipment($orderShipment);
        $this->manager->flush();
        $this->manager->clear();

        $client = $this->loginAs($this->customer)
                       ->sendRequest('GET', "/orders/{$order->getIdentifier()}");

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);

        self::assertTrue($response['succeed']);
        self::assertEquals('Response successfully returned', $response['message']);

        $metas = $response['metas'];

        self::assertArrayHasKey('invoiceDownloadable', $metas);
        self::assertIsBool($metas['invoiceDownloadable']);

        $result = $response['results'];

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('status', $result);
        self::assertArrayHasKey('identifier', $result);
        self::assertArrayHasKey('subtotal', $result);
        self::assertArrayHasKey('grandTotal', $result);
        self::assertArrayHasKey('discountTotal', $result);
        self::assertArrayHasKey('payable', $result);

        self::assertArrayHasKey('shipments', $result);
        self::assertIsArray($result['shipments']);
        self::assertNotEmpty($result['shipments']);
        $shipment = $result['shipments'][0];
        self::assertArrayHasKey('isPaid', $shipment);
        self::assertIsBool($shipment['isPaid']);
        self::assertArrayHasKey('grandTotal', $shipment);
        self::assertArrayHasKey('status', $shipment);
        self::assertArrayHasKey('method', $shipment);
        self::assertArrayHasKey('name', $shipment['method']);
        self::assertArrayHasKey('description', $shipment);
        self::assertArrayHasKey('orderItems', $shipment);
        self::assertIsArray($shipment['orderItems']);
        self::assertNotEmpty($shipment['orderItems']);

        $orderItem = $shipment['orderItems'][0];
        self::assertArrayHasKey('subtotal', $orderItem);
        self::assertArrayHasKey('grandTotal', $orderItem);
        self::assertArrayHasKey('quantity', $orderItem);
        self::assertArrayHasKey('price', $orderItem);
        self::assertArrayHasKey('inventory', $orderItem);
        self::assertArrayHasKey('variant', $orderItem['inventory']);
        self::assertArrayHasKey('product', $orderItem['inventory']['variant']);
        self::assertArrayHasKey('title', $orderItem['inventory']['variant']['product']);
        self::assertArrayHasKey('featuredImage', $orderItem['inventory']['variant']['product']);
        self::assertArrayHasKey('path', $orderItem['inventory']['variant']['product']['featuredImage']);
        self::assertArrayHasKey('alt', $orderItem['inventory']['variant']['product']['featuredImage']);

        self::assertArrayHasKey('orderAddress', $result);
        self::assertIsArray($result['orderAddress']);

        self::assertArrayHasKey('orderDocument', $result);
        self::assertIsArray($result['orderDocument']);

        self::assertArrayHasKey('createdAt', $result);
        self::assertArrayHasKey('isPaid', $result);
    }

    public function testOrderNotFound(): void
    {
        [$order] = $this->getService(QueryBuilderFilterService::class)
                        ->filter(Order::class, ['filter' => ['customer.id' => ['neq' => $this->customer->getId()]]])
                        ->getQuery()
                        ->getResult();

        $response = $this->loginAs($this->customer)
                         ->sendRequest(
                             'GET',
                             $this->router->generate('customer.orders.show', ['identifier' => $order->getIdentifier()])
                         );

        self::assertEquals(404, $response->getResponse()->getStatusCode());
    }

    public function testItCheckCustomerProfileCompleteness()
    {
        $this->customer->setName(null);
        $this->customer->setFamily(null);
        $this->customer->setNationalNumber(null);

        $this->manager->flush();
        $this->makeSureOrderItemsWillPassConditions();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getService(QueryBuilderFilterService::class)
                             ->filter(
                                 CustomerAddress::class,
                                 [
                                     'filter' => [
                                         'customer'  => $this->customer,
                                         'city.name' => CityDictionary::TEHRAN_NAME,
                                     ],
                                 ]
                             );

        $customerAddress = $queryBuilder->getQuery()->setMaxResults(1)->getSingleResult();

        AbstractPartialShipment::resetId();

        /** @var AbstractPartialShipment[] $shipments */
        $shipments = $this->getService(PartialShipmentService::class)->createFromCart(
            $this->customer->getCart(),
            $customerAddress,
            $customerAddress->getCity()->isExpress()
        );

        $deliveryDates = [];
        foreach ($shipments as $i => $shipment) {
            /** @var ExpressPartialDeliveryDate[] $calculatedDeliveryDates */
            $calculatedDeliveryDates = $shipment->getCalculatedDeliveryDates();
            $datetime                = $calculatedDeliveryDates[0]->getDeliveryDate()->format('Y-m-d');

            foreach ($calculatedDeliveryDates[0]->getPeriods() as $period) {
                if ($period->isSelectable()) {
                    $datetime = sprintf('%s %s', $datetime, $period->getStart()->format('H:i'));

                    break;
                }
            }

            $deliveryDates[] = ['id' => ++$i, 'deliveryDate' => $datetime];
        }

        $this->manager->clear();

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => $deliveryDates,
            ]
        );

        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);
        self::assertArrayHasKey('customerProfile', $response['results']);
        self::assertIsArray($response['results']['customerProfile']);
        self::assertContains('Customer profile is not completed', $response['results']['customerProfile']);
    }

    public function testItCheckMinimumCart(): void
    {
        $this->makeSureOrderItemsWillPassConditions();
        $this->makeSureOrderGrandTotalLowerThanTheMinimumCart($this->customer->getCart());

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getService(QueryBuilderFilterService::class)
                             ->filter(
                                 CustomerAddress::class,
                                 [
                                     'filter' => [
                                         'customer'  => $this->customer,
                                         'city.name' => CityDictionary::TEHRAN_NAME,
                                     ],
                                 ]
                             );

        $customerAddress = $queryBuilder->getQuery()->setMaxResults(1)->getSingleResult();

        AbstractPartialShipment::resetId();

        /** @var AbstractPartialShipment[] $shipments */
        $shipments = $this->getService(PartialShipmentService::class)->createFromCart(
            $this->customer->getCart(),
            $customerAddress,
            $customerAddress->getCity()->isExpress()
        );

        $deliveryDates = [];
        foreach ($shipments as $i => $shipment) {
            /** @var ExpressPartialDeliveryDate[] $calculatedDeliveryDates */
            $calculatedDeliveryDates = $shipment->getCalculatedDeliveryDates();
            $datetime                = $calculatedDeliveryDates[0]->getDeliveryDate()->format('Y-m-d');

            foreach ($calculatedDeliveryDates[0]->getPeriods() as $period) {
                if ($period->isSelectable()) {
                    $datetime = sprintf('%s %s', $datetime, $period->getStart()->format('H:i'));

                    break;
                }
            }

            $deliveryDates[] = ['id' => ++$i, 'deliveryDate' => $datetime];
        }

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => $deliveryDates,
                'isLegal'         => 1
            ]
        );
        self::assertEquals(422, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertArrayHasKey('message', $response);
        self::assertArrayHasKey('results', $response);
        self::assertArrayHasKey('metas', $response);
    }

    public function testValidationFailureOnStoreOrderWhenIsLegalParameterInvalid()
    {
        $this->makeSureOrderItemsWillPassConditions();

        $customers = $this->manager->getRepository(Customer::class)->findAll();

        $customer = collect($customers)
            ->sort(fn(Customer $x, Customer $y) => $x->getId() - $y->getId())
            ->last();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getService(QueryBuilderFilterService::class)
                             ->filter(
                                 CustomerAddress::class,
                                 [
                                     'filter' => [
                                         'customer'  => $customer,
                                         'city.name' => CityDictionary::TEHRAN_NAME,
                                     ],
                                 ]
                             );

        $customerAddress = $queryBuilder->getQuery()->setMaxResults(1)->getSingleResult();

        AbstractPartialShipment::resetId();

        /** @var AbstractPartialShipment[] $shipments */
        $shipments = $this->getService(PartialShipmentService::class)->createFromCart(
            $customer->getCart(),
            $customerAddress,
            $customerAddress->getCity()->isExpress()
        );

        $deliveryDates = [];
        foreach ($shipments as $i => $shipment) {
            /** @var ExpressPartialDeliveryDate[] $calculatedDeliveryDates */
            $calculatedDeliveryDates = $shipment->getCalculatedDeliveryDates();
            $datetime                = $calculatedDeliveryDates[0]->getDeliveryDate()->format('Y-m-d');

            foreach ($calculatedDeliveryDates[0]->getPeriods() as $period) {
                if ($period->isSelectable()) {
                    $datetime = sprintf('%s %s', $datetime, $period->getStart()->format('H:i'));

                    break;
                }
            }

            $deliveryDates[] = ['id' => ++$i, 'deliveryDate' => $datetime];
        }

        $client = $this->loginAs($customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => $deliveryDates,
                'isLegal'         => 1
            ]
        );

        self::assertEquals(400, $client->getResponse()->getStatusCode());

        $response = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $response);
        self::assertFalse($response['succeed']);
        self::assertArrayHasKey('message', $response);
        self::assertEquals('Customer order can not be legal!', $response['message']);
        self::assertArrayHasKey('results', $response);
        self::assertEquals([], $response['results']);
        self::assertArrayHasKey('metas', $response);
        self::assertEquals([], $response['metas']);
    }

    public function testStoreForExpressZone(): void
    {
        $this->makeSureOrderItemsWillPassConditions();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getService(QueryBuilderFilterService::class)
                             ->filter(
                                 CustomerAddress::class,
                                 [
                                     'filter' => [
                                         'customer'  => $this->customer,
                                         'city.name' => CityDictionary::TEHRAN_NAME,
                                     ],
                                 ]
                             );

        $customerAddress = $queryBuilder->getQuery()->setMaxResults(1)->getSingleResult();

        AbstractPartialShipment::resetId();

        /** @var AbstractPartialShipment[] $shipments */
        $shipments = $this->getService(PartialShipmentService::class)->createFromCart(
            $this->customer->getCart(),
            $customerAddress,
            $customerAddress->getCity()->isExpress()
        );

        $deliveryDates = [];
        foreach ($shipments as $i => $shipment) {
            /** @var ExpressPartialDeliveryDate[] $calculatedDeliveryDates */
            $calculatedDeliveryDates = $shipment->getCalculatedDeliveryDates();
            $datetime                = $calculatedDeliveryDates[0]->getDeliveryDate()->format('Y-m-d');

            foreach ($calculatedDeliveryDates[0]->getPeriods() as $period) {
                if ($period->isSelectable()) {
                    $datetime = sprintf('%s %s', $datetime, $period->getStart()->format('H:i'));

                    break;
                }
            }

            $deliveryDates[] = ['id' => ++$i, 'deliveryDate' => $datetime];
        }

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => $deliveryDates,
                'isLegal'         => 1
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);
        self::assertArrayHasKey('identifier', $result['results']);
        self::assertArrayHasKey('paymentMethod', $result['results']);
        self::assertIsNumeric($result['results']['identifier']);
        self::assertTrue(OrderPaymentMethod::isValid($result['results']['paymentMethod']));
    }

    public function testStoreForNoneExpressZone(): void
    {
        $this->makeSureOrderItemsWillPassConditions();

        $customerAddress = $this->getService(QueryBuilderFilterService::class)
                                ->filter(
                                    CustomerAddress::class,
                                    [
                                        'filter' => [
                                            'customer'  => $this->customer,
                                            'city.name' => ['neq' => CityDictionary::TEHRAN_NAME],
                                        ],
                                    ]
                                )
                                ->getQuery()
                                ->setMaxResults(1)
                                ->getSingleResult();

        $cartItemsCount           = $this->customer->getCartOrFail()->getCartItems()->count();
        $beforeOrderItemLogsCount = $this->orderItemLogCount();

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => [],
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertEquals($cartItemsCount + $beforeOrderItemLogsCount, $this->orderItemLogCount());

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);
        self::assertArrayHasKey('identifier', $result['results']);
        self::assertArrayHasKey('paymentMethod', $result['results']);
        self::assertIsNumeric($result['results']['identifier']);
        self::assertTrue(OrderPaymentMethod::isValid($result['results']['paymentMethod']));
    }

    public function testStoreWithForeignerAddress(): void
    {
        $this->makeSureOrderItemsWillPassConditions();

        $customerAddress = $this->getService(QueryBuilderFilterService::class)
                                ->filter(
                                    CustomerAddress::class,
                                    [
                                        'filter' => [
                                            'customer'  => $this->customer,
                                            'city.name' => ['neq' => CityDictionary::TEHRAN_NAME],
                                        ],
                                    ]
                                )
                                ->getQuery()
                                ->setMaxResults(1)
                                ->getSingleResult();
        $customerAddress->setIsForeigner(true);
        $customerAddress->setPervasiveCode('123456789023456');

        $cartItemsCount           = $this->customer->getCartOrFail()->getCartItems()->count();
        $beforeOrderItemLogsCount = $this->orderItemLogCount();

        $client = $this->loginAs($this->customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => [],
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertEquals($cartItemsCount + $beforeOrderItemLogsCount, $this->orderItemLogCount());

        $orderAddress = $this->lastEntity(OrderAddress::class);
        self::assertTrue($orderAddress->getIsForeigner());
        self::assertEquals('123456789023456', $orderAddress->getPervasiveCode());
    }

    public function testAutoConfirmOrderWithOfflinePaymentMethod(): void
    {
        $this->makeSureOrderItemsWillPassConditions();
        $order = $this->createDeliveredOrder();

        /** @var CustomerAddress $customerAddress */
        $customerAddress = $this->getService(QueryBuilderFilterService::class)
                                ->filter(CustomerAddress::class, [
                                    'filter' => [
                                        'customer'               => $this->customer,
                                        'city.name'              => CityDictionary::TEHRAN_NAME,
                                        'customer.orders.status' => OrderStatus::DELIVERED,
                                    ],
                                ])
                                ->getQuery()
                                ->setMaxResults(1)
                                ->getSingleResult();

        $orderAddress = (new OrderAddress())
            ->setCustomerAddress($customerAddress)
            ->setCity($customerAddress->getCity())
            ->setCoordinates($customerAddress->getCoordinates())
            ->setDistrict($customerAddress->getDistrict())
            ->setFamily($customerAddress->getFamily())
            ->setName($customerAddress->getName())
            ->setNationalCode($customerAddress->getNationalCode())
            ->setFullAddress($customerAddress->getFullAddress())
            ->setNumber($customerAddress->getNumber())
            ->setPhone($customerAddress->getMobile())
            ->setPostalCode($customerAddress->getPostalCode())
            ->setUnit($customerAddress->getUnit())
            ->setFloor($customerAddress->getFloor())
            ->setOrder($order);

        $cart = $this->customer->getCart();

        $cartItems  = $cart->getCartItems();
        $countItems = $cartItems->count();
        foreach ($cartItems as $key => $item) {
            if ($key === $countItems - 1) {
                continue;
            }

            $cart->removeCartItem($item);
        }

        $this->manager->persist($orderAddress);
        $this->manager->flush();
        $this->manager->clear();

        AbstractPartialShipment::resetId();

        /** @var AbstractPartialShipment[] $shipments */
        $shipments = $this->getService(PartialShipmentService::class)->createFromCart(
            $this->customer->getCart(),
            $customerAddress,
            $customerAddress->getCity()->isExpress()
        );

        $deliveryDates = [];
        foreach ($shipments as $i => $shipment) {
            /** @var ExpressPartialDeliveryDate[] $calculatedDeliveryDates */
            $calculatedDeliveryDates = $shipment->getCalculatedDeliveryDates();
            $datetime                = $calculatedDeliveryDates[0]->getDeliveryDate()->format('Y-m-d');

            foreach ($calculatedDeliveryDates[0]->getPeriods() as $period) {
                if ($period->isSelectable()) {
                    $datetime = sprintf('%s %s', $datetime, $period->getStart()->format('H:i'));

                    break;
                }
            }

            $deliveryDates[] = ['id' => ++$i, 'deliveryDate' => $datetime];
        }

        $client = $this->loginAs($this->customer)->sendRequest('POST', '/orders', [
            'paymentType'     => OrderPaymentMethod::OFFLINE,
            'customerAddress' => $customerAddress->getId(),
            'shipments'       => $deliveryDates,
        ]);

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);
        self::assertArrayHasKey('identifier', $result['results']);
        self::assertArrayHasKey('paymentMethod', $result['results']);
        self::assertIsNumeric($result['results']['identifier']);
        self::assertTrue(OrderPaymentMethod::isValid($result['results']['paymentMethod']));

        $order = $this->manager->getRepository(Order::class)
                               ->findOneBy(['identifier' => $result['results']['identifier']]);

        self::assertEquals(OrderStatus::CONFIRMED, $order->getStatus());
    }

    public function testStoreForNoneExpressZoneWithCoupon(): void
    {
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234570']);
        $this->makeSureOrderItemsWillPassConditions($customer);

        $customerAddress = $this->getService(QueryBuilderFilterService::class)
                                ->filter(
                                    CustomerAddress::class,
                                    [
                                        'filter' => [
                                            'customer.id' => $customer->getId(),
                                            'city.name'   => ['neq' => CityDictionary::TEHRAN_NAME],
                                        ],
                                    ]
                                )
                                ->getQuery()
                                ->setMaxResults(1)
                                ->getSingleResult();

        $cartItemsCount           = $customer->getCartOrFail()->getCartItems()->count();
        $beforeOrderItemLogsCount = $this->orderItemLogCount();

        /** @var PromotionCoupon $coupon */
        $coupon = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'first_order']);
        $this->updatePromotionRuleConfigurationForCustomer($coupon, $customer, $customerAddress);
        $cart = $customer->getCart();
        $cart->setPromotionCoupon($coupon);
        /** @var CartService $cartService */
        $cartService = $this->getService(CartService::class);
        $cartService->save($cart);
        $cartService->view(['address' => $customerAddress], $cart);

        $client = $this->loginAs($customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => [],
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertEquals($cartItemsCount + $beforeOrderItemLogsCount, $this->orderItemLogCount());

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);
        self::assertArrayHasKey('identifier', $result['results']);
        self::assertArrayHasKey('paymentMethod', $result['results']);
        self::assertIsNumeric($result['results']['identifier']);
        self::assertTrue(OrderPaymentMethod::isValid($result['results']['paymentMethod']));

        $order = $this->getService(\App\Repository\OrderRepository::class)
                      ->findOneBy(['customer' => $customer], ['id' => 'desc']);

        self::assertEquals(510000, $order->getGrandTotal());
        self::assertEquals(511000, $order->getSubtotal());
        self::assertEquals(1000, $order->getDiscountTotal());
        self::assertCount(1, $order->getDiscounts());
    }

    public function testStoreForNoneExpressZoneWithCouponWithPercentageDiscountAction(): void
    {
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234570']);
        $this->makeSureOrderItemsWillPassConditions($customer);

        $customerAddress = $this->getService(QueryBuilderFilterService::class)
                                ->filter(
                                    CustomerAddress::class,
                                    [
                                        'filter' => [
                                            'customer.id' => $customer->getId(),
                                            'city.name'   => ['neq' => CityDictionary::TEHRAN_NAME],
                                        ],
                                    ]
                                )
                                ->getQuery()
                                ->setMaxResults(1)
                                ->getSingleResult();

        $cartItemsCount           = $customer->getCartOrFail()->getCartItems()->count();
        $beforeOrderItemLogsCount = $this->orderItemLogCount();

        /** @var PromotionCoupon $coupon */
        $coupon = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'first_order']);
        $this->updatePromotionRuleConfigurationForCustomer($coupon, $customer, $customerAddress);
        $promotion    = $coupon->getPromotion();
        $discountRule = $promotion->getActions()->first();
        $discountRule->setType('percentage_discount');
        $discountRule->setConfiguration(['ratio' => 50, 'max_amount' => 100000000]);
        $this->manager->flush();
        $cart = $customer->getCart();
        $cart->setPromotionCoupon($coupon);
        /** @var CartService $cartService */
        $cartService = $this->getService(CartService::class);
        $cartService->save($cart);
        $cartService->view(['address' => $customerAddress], $cart);

        $client = $this->loginAs($customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => [],
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertEquals($cartItemsCount + $beforeOrderItemLogsCount, $this->orderItemLogCount());

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);
        self::assertArrayHasKey('identifier', $result['results']);
        self::assertArrayHasKey('paymentMethod', $result['results']);
        self::assertIsNumeric($result['results']['identifier']);
        self::assertTrue(OrderPaymentMethod::isValid($result['results']['paymentMethod']));

        $order = $this->getService(\App\Repository\OrderRepository::class)
                      ->findOneBy(['customer' => $customer], ['id' => 'desc']);

        self::assertEquals(255500, $order->getGrandTotal());
        self::assertEquals(511000, $order->getSubtotal());
        self::assertEquals(255500, $order->getDiscountTotal());
        self::assertCount(1, $order->getDiscounts());
    }

    public function testStoreForNoneExpressZoneWithCouponWithMultipleShipment(): void
    {
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234572']);
        $this->makeSureOrderItemsWillPassConditions($customer);

        $customerAddress = $this->getService(QueryBuilderFilterService::class)
                                ->filter(
                                    CustomerAddress::class,
                                    [
                                        'filter' => [
                                            'customer.id' => $customer->getId(),
                                            'city.name'   => ['neq' => CityDictionary::TEHRAN_NAME],
                                        ],
                                    ]
                                )
                                ->getQuery()
                                ->setMaxResults(1)
                                ->getSingleResult();

        $cartItemsCount           = $customer->getCartOrFail()->getCartItems()->count();
        $beforeOrderItemLogsCount = $this->orderItemLogCount();

        /** @var PromotionCoupon $coupon */
        $coupon         = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'everyone']);
        $cart           = $customer->getCart();
        $cartProductIds = $customer->getCart()->getCartItems()->map(
            fn($item) => $item->getInventory()->getVariant()->getProduct()->getId()
        )->toArray();
        $promotionRule  = (new PromotionRule())->setType('product')->setConfiguration(['product_ids' => $cartProductIds]);
        $coupon->getPromotion()->addRule($promotionRule);
        $coupon->getPromotion()->getActions()->first()->setConfiguration(['amount' => 200000]);
        $this->manager->flush();
        $cart->setPromotionCoupon($coupon);
        foreach ($cart->getCartItems() as $cartItem) {
            $cartItem->getInventory()->setIsActive(true);
        }
        /** @var CartService $cartService */
        $cartService = $this->getService(CartService::class);
        $cartService->save($cart);
        $cartService->view(['address' => $customerAddress], $cart);

        $client = $this->loginAs($customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => [],
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertEquals($cartItemsCount + $beforeOrderItemLogsCount, $this->orderItemLogCount());

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);
        self::assertArrayHasKey('identifier', $result['results']);
        self::assertArrayHasKey('paymentMethod', $result['results']);
        self::assertIsNumeric($result['results']['identifier']);
        self::assertTrue(OrderPaymentMethod::isValid($result['results']['paymentMethod']));

        $order = $this->getService(\App\Repository\OrderRepository::class)
                      ->findOneBy(['customer' => $customer], ['id' => 'desc']);

        self::assertEquals(403000, $order->getGrandTotal());
        self::assertEquals(610000, $order->getSubtotal());
        self::assertEquals(200000, $order->getDiscountTotal());
        self::assertCount(2, $order->getDiscounts());
        self::assertInstanceOf(OrderShipment::class, $order->getDiscounts()->first()->getOrderShipment());
        self::assertEquals($order->getDiscounts()->first()->getAmount(), $order->getDiscounts()->first()->getUnitAmount() * $order->getDiscounts()->first()->getQuantity());
        self::assertInstanceOf(OrderShipment::class, $order->getDiscounts()[1]->getOrderShipment());
        self::assertEquals($order->getDiscounts()[1]->getAmount(), $order->getDiscounts()[1]->getUnitAmount() * $order->getDiscounts()[1]->getQuantity());
    }

    public function testItCheckZeroAmount()
    {
        $customer = $this->getService(CustomerRepository::class)->findOneBy(['mobile' => '09121234570']);
        $this->makeSureOrderItemsWillPassConditions($customer);
        $cartItem = $customer->getCart()->getCartItems()->first();
        $cartItem->setGrandTotal($cartItem->getInventory()->getFinalPrice());
        $this->manager->flush();
        $customerAddress = $this->getService(QueryBuilderFilterService::class)
                                ->filter(
                                    CustomerAddress::class,
                                    [
                                        'filter' => [
                                            'customer.id' => $customer->getId(),
                                            'city.name'   => ['neq' => CityDictionary::TEHRAN_NAME],
                                        ],
                                    ]
                                )
                                ->getQuery()
                                ->setMaxResults(1)
                                ->getSingleResult();

        $cartItemsCount           = $customer->getCartOrFail()->getCartItems()->count();
        $beforeOrderItemLogsCount = $this->orderItemLogCount();

        /** @var PromotionCoupon $coupon */
        $coupon = $this->getService(PromotionCouponRepository::class)->findOneBy(['code' => 'second_order']);
        $this->updatePromotionRuleConfigurationForCustomer($coupon, $customer, $customerAddress);
        $cart = $customer->getCart();
        $cart->setPromotionCoupon($coupon);
        /** @var CartService $cartService */
        $cartService = $this->getService(CartService::class);
        $cartService->save($cart);
        $cartService->view(['address' => $customerAddress], $cart);

        $cart->setGrandTotal(510000);
        $this->manager->flush();

        $client = $this->loginAs($customer)->sendRequest(
            'POST',
            $this->router->generate('customer.orders.store'),
            [
                'paymentType'     => OrderPaymentMethod::ONLINE,
                'customerAddress' => $customerAddress->getId(),
                'shipments'       => [],
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertEquals($cartItemsCount + $beforeOrderItemLogsCount, $this->orderItemLogCount());

        $result = $this->getControllerResponse();

        self::assertArrayHasKey('succeed', $result);
        self::assertTrue($result['succeed']);
        self::assertArrayHasKey('message', $result);
        self::assertEquals('Response successfully returned', $result['message']);
        self::assertArrayHasKey('results', $result);
        self::assertIsArray($result['results']);
        self::assertArrayHasKey('identifier', $result['results']);
        self::assertArrayHasKey('paymentMethod', $result['results']);
        self::assertIsNumeric($result['results']['identifier']);
        self::assertTrue(OrderPaymentMethod::isValid($result['results']['paymentMethod']));

        $order = $this->getService(\App\Repository\OrderRepository::class)
                      ->findOneBy(['customer' => $customer], ['id' => 'desc']);

        self::assertEquals(0, $order->getGrandTotal());
        self::assertEquals(511000, $order->getSubtotal());
        self::assertEquals(511000, $order->getDiscountTotal());
        self::assertCount(1, $order->getDiscounts());
        self::assertEquals(OrderStatus::WAIT_CUSTOMER, $order->getStatus());
        self::assertEquals(OrderPaymentMethod::OFFLINE, $order->getPaymentMethod());
    }

    private function makeSureOrderItemsWillPassConditions($customer = null): void
    {
        if ($customer === null) {
            $customer = $this->customer;
        }

        $cart = $customer->getCartOrFail();

        foreach ($cart->getCartItems() as $cartItem) {
            $inventory = $cartItem->getInventory();
            $inventory->setSellerStock(100);
            $inventory->setMaxPurchasePerOrder(100);

            $cartItem->setPrice($inventory->getPrice());

            $product = $inventory->getVariant()->getProduct();
            $product->setIsActive(true);
            $product->setStatus(ProductStatusDictionary::CONFIRMED);
            if ($product->getCategory()->getCommission() === null) {
                $product->getCategory()->setCommission(10);
            } else {
                $product->getCategory()->setCommission(10);
            }
        }

        $this->manager->flush();
    }

    private function makeSureOrderGrandTotalLowerThanTheMinimumCart(Cart $cart): void
    {
        foreach ($cart->getCartItems() as $cartItem) {
            $cartItem->setGrandTotal(100);
        }
        $this->manager->flush();
    }

    private function orderItemLogCount()
    {
        $queryBuilder = $this->getService(QueryBuilderFilterService::class)
                             ->filter(
                                 \App\Entity\OrderItemLog::class
                             );

        return (int)$queryBuilder
            ->select(sprintf('count(%s.id)', $queryBuilder->getRootAliases()[0]))
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createDeliveredOrder(): Order
    {
        $cart = $this->customer->getCartOrFail();

        $order = (new Order())
            ->setCustomer($this->customer)
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

        $this->manager->persist($order);
        $this->manager->persist($orderDocument);
        $this->manager->flush();

        return $order;
    }
}
