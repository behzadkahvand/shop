<?php

namespace App\Tests\Unit\Service\Order\AutoConfirm;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderAddress;
use App\Entity\OrderShipment;
use App\Repository\OrderRepository;
use App\Service\Order\AutoConfirm\AutoConfirmOrderService;
use App\Service\Order\OrderIsNotConfirmableException;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use Doctrine\Common\Collections\ArrayCollection;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

/**
 * Class AutoConfirmOrderServiceTest
 */
final class AutoConfirmOrderServiceTest extends MockeryTestCase
{
    public function testItReturnFalseIfOrderIsConfirmedAndCalledIsConfirmable(): void
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive(['getStatus' => OrderStatus::CONFIRMED, 'getIdentifier' => 1])->once()->withNoArgs();

        $service = new AutoConfirmOrderService(
            Mockery::mock(CreateOrderStatusLogService::class),
            Mockery::mock(OrderRepository::class),
            Mockery::mock(OrderShipmentStatusService::class)
        );

        self::assertFalse($service->isConfirmable($order));
    }

    /**
     * @dataProvider orderWithOfflinePaymentMethodDataProvider
     *
     * @param array $deliveredOrders
     */
    public function testItCheckOrderWithOfflinePaymentMethodIsConfirmable(array $deliveredOrders): void
    {
        $customer = Mockery::mock(Customer::class);
        $point    = Mockery::mock(AbstractPoint::class);

        $orderAddress = Mockery::mock(OrderAddress::class);
        $orderAddress->shouldReceive('getCoordinates')->once()->withNoArgs()->andReturn($point);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive([
            'getStatus'        => OrderStatus::WAIT_CUSTOMER,
            'getPaymentMethod' => OrderPaymentMethod::OFFLINE,
            'getOrderAddress'  => $orderAddress,
            'getCustomer'      => $customer,
            'getIdentifier'    => 1,
        ])->once()->withNoArgs();

        $orderRepository = Mockery::mock(OrderRepository::class);
        $orderRepository->shouldReceive('successCustomerOrdersByPoint')
                        ->once()
                        ->with($point, $customer)
                        ->andReturn($deliveredOrders);

        $service = new AutoConfirmOrderService(
            Mockery::mock(CreateOrderStatusLogService::class),
            $orderRepository,
            Mockery::mock(OrderShipmentStatusService::class)
        );

        $expectedResult = 0 < count($deliveredOrders);

        self::assertEquals($expectedResult, $service->isConfirmable($order));
    }

    public function orderWithOfflinePaymentMethodDataProvider(): iterable
    {
        return [
            [[]],
            [[new stdClass()]],
        ];
    }

    /**
     * @dataProvider orderWithOnlinePaymentMethodDataProvider
     *
     * @param string $initialOrderStatus
     * @param array $methods
     * @param array $deliveredOrders
     * @param bool $expectedResult
     */
    public function testItCheckOrderWithOnlinePaymentMethodIsConfirmable(
        string $initialOrderStatus,
        array $methods,
        array $deliveredOrders,
        bool $expectedResult
    ): void {
        $customer = Mockery::mock(Customer::class);
        $point    = Mockery::mock(AbstractPoint::class);

        $orderAddress = Mockery::mock(OrderAddress::class);
        $orderAddress->shouldReceive('getCoordinates')->atMost(1)->withNoArgs()->andReturn($point);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getStatus')->twice()->withNoArgs()->andReturn($initialOrderStatus);
        $order->shouldReceive(array_merge($methods, [
            'getOrderAddress' => $orderAddress,
            'getCustomer'     => $customer,
            'getIdentifier'   => 1,
        ]))->atMost(1)->withNoArgs();

        $orderRepository = Mockery::mock(OrderRepository::class);
        $orderRepository->shouldReceive('successCustomerOrdersByPoint')
                        ->atMost(1)
                        ->with($point, $customer)
                        ->andReturn($deliveredOrders);

        $service = new AutoConfirmOrderService(
            Mockery::mock(CreateOrderStatusLogService::class),
            $orderRepository,
            Mockery::mock(OrderShipmentStatusService::class)
        );

        self::assertEquals($expectedResult, $service->isConfirmable($order));
    }

    public function orderWithOnlinePaymentMethodDataProvider(): iterable
    {
        yield [OrderStatus::WAITING_FOR_PAY, ['getPaymentMethod' => OrderPaymentMethod::ONLINE], [], false];
        yield [OrderStatus::WAITING_FOR_PAY, ['getPaymentMethod' => OrderPaymentMethod::ONLINE], [], false];
        yield [OrderStatus::WAITING_FOR_PAY, ['getPaymentMethod' => OrderPaymentMethod::ONLINE], [], false];
        yield [OrderStatus::WAITING_FOR_PAY, ['getPaymentMethod' => OrderPaymentMethod::ONLINE], [], false];
        yield [
            OrderStatus::WAIT_CUSTOMER,
            ['getPaymentMethod' => OrderPaymentMethod::ONLINE, 'isPaid' => true],
            [new stdClass()],
            true,
        ];
        yield [
            OrderStatus::WAIT_CUSTOMER,
            ['getPaymentMethod' => OrderPaymentMethod::ONLINE, 'isPaid' => true],
            [],
            false,
        ];
        yield [
            OrderStatus::WAIT_CUSTOMER,
            ['getPaymentMethod' => OrderPaymentMethod::ONLINE, 'isPaid' => false],
            [],
            false,
        ];
        yield [
            OrderStatus::WAIT_CUSTOMER,
            ['getPaymentMethod' => OrderPaymentMethod::ONLINE, 'isPaid' => false],
            [new stdClass()],
            false,
        ];
    }

    public function testItThrowExceptionIOrderIsNotConfirmableButConfirmMethodIsCalled(): void
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive(['getStatus' => OrderStatus::CONFIRMED])->once()->withNoArgs();
        $order->shouldReceive(['getIdentifier' => 1])->twice()->withNoArgs();

        $service = new AutoConfirmOrderService(
            Mockery::mock(CreateOrderStatusLogService::class),
            Mockery::mock(OrderRepository::class),
            Mockery::mock(OrderShipmentStatusService::class)
        );

        $this->expectException(OrderIsNotConfirmableException::class);
        $this->expectExceptionMessage('Order with identifier 1 is not confirmable.');

        $service->confirm($order);
    }

    public function testItConfirmAConfirmableOrder(): void
    {
        $orderIdentifier = random_int(100, 200);
        $customer        = Mockery::mock(Customer::class);
        $point           = Mockery::mock(AbstractPoint::class);
        $shipment        = Mockery::mock(OrderShipment::class);

        $orderAddress = Mockery::mock(OrderAddress::class);
        $orderAddress->shouldReceive('getCoordinates')->once()->withNoArgs()->andReturn($point);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getStatus')->times(3)->withNoArgs()->andReturn(OrderStatus::WAIT_CUSTOMER);
        $order->shouldReceive('setStatus')->once()->with(OrderStatus::CONFIRMED)->andReturnSelf();
        $order->shouldReceive('getShipments')->once()->withNoArgs()->andReturn(new ArrayCollection([$shipment]));

        $order->shouldReceive([
            'getPaymentMethod' => OrderPaymentMethod::ONLINE,
            'getIdentifier'    => $orderIdentifier,
            'isPaid'           => true,
            'getOrderAddress'  => $orderAddress,
            'getCustomer'      => $customer,
        ])->between(1, 2)->withNoArgs();

        $orderRepository = Mockery::mock(OrderRepository::class);
        $orderRepository->shouldReceive('successCustomerOrdersByPoint')
                        ->atMost(1)
                        ->with($point, $customer)
                        ->andReturn([new stdClass()]);

        $createOrderStatusLogService = Mockery::mock(CreateOrderStatusLogService::class);
        $createOrderStatusLogService->shouldReceive('perform')
                                    ->once()
                                    ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false);

        $orderShipmentStatusService = Mockery::mock(OrderShipmentStatusService::class);
        $orderShipmentStatusService->shouldReceive('change')
                                   ->once()
                                   ->with($shipment, OrderShipmentStatus::WAITING_FOR_SUPPLY)
                                   ->andReturn();

        $service = new AutoConfirmOrderService(
            $createOrderStatusLogService,
            $orderRepository,
            $orderShipmentStatusService
        );

        $service->confirm($order);
    }
}
