<?php

namespace App\Tests\Unit\Service\Order\OrderStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerPackageStatus;
use App\Dictionary\SellerPackageType;
use App\Dictionary\ShippingCategoryName;
use App\Entity\Admin;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\Seller;
use App\Entity\SellerOrderItem;
use App\Entity\SellerPackage;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\Order\OrderStatus\WaitCustomerOrderStatus;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Service\Seller\SellerPackage\SellerPackageFactory;
use App\Service\Seller\SellerPackage\Status\SellerPackageStatusService;
use App\Service\Seller\SellerPackage\ValidationStrategy\SellerOrderItemValidationStrategyInterface;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use ReflectionClass;
use Symfony\Component\Security\Core\Security;

class WaitCustomerOrderStatusTest extends BaseUnitTestCase
{
    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected LegacyMockInterface|MockInterface|OrderShipmentStatusService|null $orderShipmentStatusService;

    protected LegacyMockInterface|MockInterface|SellerPackageFactory|null $packageFactory;

    protected SellerPackageStatusService|LegacyMockInterface|MockInterface|null $packageStatusService;

    protected LegacyMockInterface|MockInterface|SellerOrderItemStatusService|null $sellerOrderItemStatusService;

    protected LegacyMockInterface|MockInterface|Security|null $security;

    protected ?WaitCustomerOrderStatus $waitCustomerOrderStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderMock = Mockery::mock(Order::class);

        $this->orderShipmentStatusService   = Mockery::mock(OrderShipmentStatusService::class);
        $this->packageFactory               = Mockery::mock(SellerPackageFactory::class);
        $this->packageStatusService         = Mockery::mock(SellerPackageStatusService::class);
        $this->security                     = Mockery::mock(Security::class);
        $this->sellerOrderItemStatusService = Mockery::mock(SellerOrderItemStatusService::class);

        $this->waitCustomerOrderStatus = new WaitCustomerOrderStatus(
            $this->orderShipmentStatusService,
            $this->packageFactory,
            $this->packageStatusService,
            $this->sellerOrderItemStatusService,
            $this->security
        );
    }

    public function testItCanSetOrderToCallFailedWithMock(): void
    {
        $this->orderMock->shouldReceive('setStatus')->once()->with(OrderStatus::CALL_FAILED)->andReturnSelf();

        $this->waitCustomerOrderStatus->callFailed($this->orderMock);
    }

    public function testItCanSetOrderToConfirmedWithMock(): void
    {
        $shipment = Mockery::mock(OrderShipment::class);
        $shipment->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(OrderShipmentStatus::NEW);

        $this->orderMock->shouldReceive('setStatus')->once()->with(OrderStatus::CONFIRMED)->andReturnSelf();
        $this->orderMock->shouldReceive('getShipments')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$shipment]));
        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([]));

        $this->orderShipmentStatusService->shouldReceive('change')
                                         ->once()
                                         ->with($shipment, OrderShipmentStatus::WAITING_FOR_SUPPLY)
                                         ->andReturn();

        $this->packageFactory->shouldNotReceive('create');
        $this->packageStatusService->shouldNotReceive('change');

        $this->waitCustomerOrderStatus->confirmed($this->orderMock);
    }

    public function testItCanCreatePackageForItemsWithZeroSuppliesInAndSetPackageStatusToReceived(): void
    {
        $shipment = Mockery::mock(OrderShipment::class);
        $shipment->shouldReceive('getStatus')->once()->withNoArgs()->andReturn(OrderShipmentStatus::NEW);

        $seller = Mockery::mock(Seller::class);
        $seller->shouldReceive(['getId' => 1])->once()->withNoArgs();

        $inventory = Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($seller);

        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('isWaitingForSend')->once()->withNoArgs()->andReturnTrue();
        $sellerOrderItem->shouldReceive('getPackageItem')->once()->withNoArgs()->andReturnNull();

        $shipment2 = Mockery::mock(OrderShipment::class);
        $shipment2->shouldReceive('getTitle')->once()->withNoArgs()->andReturn(ShippingCategoryName::NORMAL);

        $orderItem = Mockery::mock(OrderItem::class);
        $orderItem->shouldReceive('getLeadTime')->once()->withNoArgs()->andReturn(0);
        $orderItem->shouldReceive([
            'getSellerOrderItem' => $sellerOrderItem,
            'getInventory'       => $inventory,
            'getOrderShipment'   => $shipment2,
        ])
                  ->atMost(2)
                  ->withNoArgs();

        $this->orderMock->shouldReceive('setStatus')->once()->with(OrderStatus::CONFIRMED)->andReturnSelf();
        $this->orderMock->shouldReceive('getShipments')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$shipment]));
        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$orderItem]));

        $this->orderShipmentStatusService->shouldReceive('change')
                                         ->once()
                                         ->with($shipment, OrderShipmentStatus::WAITING_FOR_SUPPLY)
                                         ->andReturn();

        $admin = Mockery::mock(Admin::class);
        $this->security->shouldReceive(['getUser' => $admin])->once()->withNoArgs();

        $package = Mockery::mock(SellerPackage::class);
        $this->packageFactory->shouldReceive('create')
                             ->once()
                             ->with(
                                 [$sellerOrderItem],
                                 SellerPackageType::NON_FMCG,
                                 $seller,
                                 Mockery::type(SellerOrderItemValidationStrategyInterface::class),
                                 true
                             )
                             ->andReturn($package);

        $this->packageStatusService->shouldReceive('change')
                                   ->once()
                                   ->with($package, SellerPackageStatus::RECEIVED, $admin)
                                   ->andReturn();

        $this->sellerOrderItemStatusService->shouldReceive('change')
                                           ->once()
                                           ->with($sellerOrderItem, SellerOrderItemStatus::RECEIVED)
                                           ->andReturn();

        $this->waitCustomerOrderStatus->confirmed($this->orderMock);
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupport($status, $assert): void
    {
        $result = $this->waitCustomerOrderStatus->support($status);

        self::assertEquals($assert, $result);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testItThrowsExceptionWhenOrderStatusTransitionIsInvalid($method): void
    {
        $this->expectException(InvalidOrderStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order status transition is invalid!');

        $this->waitCustomerOrderStatus->{$method}($this->orderMock);
    }

    public function supportProvider()
    {
        $orderStatuses = OrderStatus::toArray();

        return array_map(function ($status) {
            return [$status, ($status === OrderStatus::WAIT_CUSTOMER)];
        }, $orderStatuses);
    }

    public function testItCanGetValidTransitions(): void
    {
        $result = $this->waitCustomerOrderStatus->validTransitions();

        self::assertEquals([
            OrderStatus::CALL_FAILED,
            OrderStatus::CONFIRMED,
            OrderStatus::CANCELED,
            OrderStatus::REFUND,
        ], $result);
    }

    public function testShouldCancelOrderAndReleaseStock(): void
    {
        $this->orderMock->shouldReceive('setStatus')
                        ->once()
                        ->with(OrderStatus::CANCELED)
                        ->andReturnSelf();

        $orderShipment = Mockery::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getStatus')
                      ->once()
                      ->withNoArgs()
                      ->andReturn(OrderShipmentStatus::WAITING_FOR_SUPPLY);

        $this->orderMock->shouldReceive('getShipments')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([$orderShipment]));

        $this->orderShipmentStatusService->shouldReceive('change')
                                         ->once()
                                         ->with($orderShipment, OrderShipmentStatus::CANCELED)
                                         ->andReturn();

        $this->orderMock->shouldReceive('releaseReservedStock')
                        ->withNoArgs()
                        ->andReturnNull();

        $this->waitCustomerOrderStatus->canceled($this->orderMock);
    }

    public function exceptionProvider()
    {
        return array_map(function ($method) {
            return [$method];
        }, [
            'new',
            'waitCustomer',
            'waitingForPay',
            'delivered',
            'canceledSystem',
        ]);
    }

    public function testGetSellerOrderItemsGroupedBySellerAndPackageType(): void
    {
        $orderItem1 = $this->makeMockedOrderItem(1, ShippingCategoryName::NORMAL);
        $orderItem2 = $this->makeMockedOrderItem(2, ShippingCategoryName::FMCG);
        $orderItem3 = $this->makeMockedOrderItem(1, ShippingCategoryName::FMCG);
        $orderItem4 = $this->makeMockedOrderItem(3, ShippingCategoryName::SUPER_HEAVY);
        $orderItem5 = $this->makeMockedOrderItem(2, ShippingCategoryName::NORMAL);
        $orderItem6 = $this->makeMockedOrderItem(1, ShippingCategoryName::HEAVY);

        $orderItems = [$orderItem1, $orderItem2, $orderItem3, $orderItem4, $orderItem5, $orderItem6];

        $waitCustomerOrderStatus = new ReflectionClass($this->waitCustomerOrderStatus);
        $method                  = $waitCustomerOrderStatus->getMethod('getSellerOrderItemsGroupedBySellerAndPackageType');
        $method->setAccessible(true);
        $responseItems = $method->invoke($this->waitCustomerOrderStatus, $orderItems);

        self::assertEquals(3, count($responseItems));
        foreach ($responseItems as $sellerId => $packageItems) {
            if ($sellerId == 1) {
                self::assertArrayHasKey(SellerPackageType::NON_FMCG, $packageItems);
                self::assertArrayHasKey(SellerPackageType::FMCG, $packageItems);
                self::assertEquals(2, count($packageItems[SellerPackageType::NON_FMCG]['sellerOrderItems']));
                self::assertEquals(1, count($packageItems[SellerPackageType::FMCG]['sellerOrderItems']));
                self::assertEquals(SellerPackageType::FMCG, $packageItems[SellerPackageType::FMCG]['packageType']);
                self::assertEquals(
                    SellerPackageType::NON_FMCG,
                    $packageItems[SellerPackageType::NON_FMCG]['packageType']
                );
                self::assertEquals(1, ($packageItems[SellerPackageType::FMCG]['seller'])->getId());
                self::assertEquals(1, ($packageItems[SellerPackageType::NON_FMCG]['seller'])->getId());
            } elseif ($sellerId == 2) {
                self::assertArrayHasKey(SellerPackageType::NON_FMCG, $packageItems);
                self::assertArrayHasKey(SellerPackageType::FMCG, $packageItems);
                self::assertEquals(1, count($packageItems[SellerPackageType::NON_FMCG]['sellerOrderItems']));
                self::assertEquals(1, count($packageItems[SellerPackageType::FMCG]['sellerOrderItems']));
                self::assertEquals(SellerPackageType::FMCG, $packageItems[SellerPackageType::FMCG]['packageType']);
                self::assertEquals(
                    SellerPackageType::NON_FMCG,
                    $packageItems[SellerPackageType::NON_FMCG]['packageType']
                );
                self::assertEquals(2, ($packageItems[SellerPackageType::FMCG]['seller'])->getId());
                self::assertEquals(2, ($packageItems[SellerPackageType::NON_FMCG]['seller'])->getId());
            } else {
                self::assertArrayHasKey(SellerPackageType::NON_FMCG, $packageItems);
                self::assertArrayNotHasKey(SellerPackageType::FMCG, $packageItems);
                self::assertEquals(1, count($packageItems[SellerPackageType::NON_FMCG]['sellerOrderItems']));
                self::assertEquals(
                    SellerPackageType::NON_FMCG,
                    $packageItems[SellerPackageType::NON_FMCG]['packageType']
                );
                self::assertEquals(3, ($packageItems[SellerPackageType::NON_FMCG]['seller'])->getId());
            }
        }
    }

    private function makeMockedOrderItem(int $sellerId, string $orderShipmentTitle): OrderItem
    {
        $seller = Mockery::mock(Seller::class);
        $seller->shouldReceive(['getId' => $sellerId])->withNoArgs();

        $inventory = Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getSeller')->once()->withNoArgs()->andReturn($seller);

        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);

        $orderShipment = Mockery::mock(OrderShipment::class);
        $orderShipment->shouldReceive('getTitle')->once()->withNoArgs()->andReturn($orderShipmentTitle);

        $orderItem = Mockery::mock(OrderItem::class);
        $orderItem->shouldReceive([
            'getSellerOrderItem' => $sellerOrderItem,
            'getInventory'       => $inventory,
            'getOrderShipment'   => $orderShipment,
        ])
                  ->once()
                  ->withNoArgs();

        return $orderItem;
    }
}
