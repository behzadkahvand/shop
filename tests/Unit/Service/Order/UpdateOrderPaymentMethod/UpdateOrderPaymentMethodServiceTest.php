<?php

namespace App\Tests\Unit\Service\Order\UpdateOrderPaymentMethod;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Order\UpdateOrderPaymentMethod\Exceptions\InvalidOrderException;
use App\Service\Order\UpdateOrderPaymentMethod\Exceptions\InvalidOrderPaymentMethodException;
use App\Service\Order\UpdateOrderPaymentMethod\Exceptions\OfflinePaymentMethodException;
use App\Service\Order\UpdateOrderPaymentMethod\UpdateOrderPaymentMethodService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class UpdateOrderPaymentMethodServiceTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|OrderRepository|null $orderRepoMock;

    protected LegacyMockInterface|MockInterface|EntityManager|null $em;

    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected ?UpdateOrderPaymentMethodService $updateOrderPaymentMethod;

    protected ?Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepoMock = Mockery::mock(OrderRepository::class);
        $this->em            = Mockery::mock(EntityManager::class);
        $this->orderMock     = Mockery::mock(Order::class);

        $this->order = new Order();

        $this->updateOrderPaymentMethod = new UpdateOrderPaymentMethodService(
            $this->orderRepoMock,
            $this->em
        );
    }

    public function testItCanUpdateOrderPaymentMethodWhenPaymentMethodIsOnlineWithOrderMock(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('setPaymentMethod')
                        ->once()
                        ->with(OrderPaymentMethod::ONLINE)
                        ->andReturn($this->orderMock);
        $this->orderMock->shouldReceive('setStatus')
                        ->once()
                        ->with(OrderStatus::WAITING_FOR_PAY)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::ONLINE);

        self::assertEquals($result, $this->orderMock);
    }

    public function testItCanUpdateOrderPaymentMethodWhenPaymentMethodIsCPGWithOrderMock(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('setPaymentMethod')
                        ->once()
                        ->with(OrderPaymentMethod::CPG)
                        ->andReturn($this->orderMock);
        $this->orderMock->shouldReceive('setStatus')
                        ->once()
                        ->with(OrderStatus::WAITING_FOR_PAY)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::CPG);

        self::assertEquals($result, $this->orderMock);
    }

    public function testItCanUpdateOrderPaymentMethodWhenPaymentMethodIsHamrahCardWithOrderMock(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('setPaymentMethod')
                        ->once()
                        ->with(OrderPaymentMethod::HAMRAH_CARD)
                        ->andReturn($this->orderMock);
        $this->orderMock->shouldReceive('setStatus')
                        ->once()
                        ->with(OrderStatus::WAITING_FOR_PAY)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::HAMRAH_CARD);

        self::assertEquals($result, $this->orderMock);
    }

    public function testItCanUpdateOrderPaymentMethodWhenPaymentMethodIsOfflineWithOrderMock(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('isPaid')
                        ->once()
                        ->withNoArgs()
                        ->andReturnFalse();
        $this->orderMock->shouldReceive('getShipmentsCount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(1);
        $this->orderMock->shouldReceive('setPaymentMethod')
                        ->once()
                        ->with(OrderPaymentMethod::OFFLINE)
                        ->andReturn($this->orderMock);
        $this->orderMock->shouldReceive('setStatus')
                        ->once()
                        ->with(OrderStatus::WAIT_CUSTOMER)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::OFFLINE);

        self::assertEquals($result, $this->orderMock);
    }

    public function testItThrowsInvalidPaymentMethodExceptionOnUpdatePaymentMethod(): void
    {
        $this->expectException(InvalidOrderPaymentMethodException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order payment method is invalid!');

        $this->updateOrderPaymentMethod->perform(1, 'INVALID');
    }

    public function testItThrowsInvalidOrderExceptionOnUpdatePaymentMethod(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturnNull();

        $this->expectException(InvalidOrderException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order is invalid for updating payment method action!');

        $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::OFFLINE);
    }

    public function testItThrowsOfflinePaymentMethodExceptionOnUpdatePaymentMethodWhenOrderIsPaid(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('isPaid')
                        ->once()
                        ->withNoArgs()
                        ->andReturnTrue();

        $this->expectException(OfflinePaymentMethodException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Offline payment method is invalid because order is paid or order has several shipments!');

        $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::OFFLINE);
    }

    public function testItThrowsOfflinePaymentMethodExceptionOnUpdatePaymentMethodWhenOrderHasSeveralShipments(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('isPaid')
                        ->once()
                        ->withNoArgs()
                        ->andReturnFalse();
        $this->orderMock->shouldReceive('getShipmentsCount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(2);

        $this->expectException(OfflinePaymentMethodException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Offline payment method is invalid because order is paid or order has several shipments!');

        $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::OFFLINE);
    }

    public function testItCanUpdateOrderPaymentMethodWhenPaymentMethodIsOnlineWithOrderObject(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->order);

        $this->order->setPaymentMethod(OrderPaymentMethod::OFFLINE)
                    ->setStatus(OrderStatus::WAIT_CUSTOMER);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::ONLINE);

        self::assertEquals($result, $this->order);
        self::assertEquals(OrderPaymentMethod::ONLINE, $result->getPaymentMethod());
        self::assertEquals(OrderStatus::WAITING_FOR_PAY, $result->getStatus());
    }

    public function testItCanUpdateOrderPaymentMethodWhenPaymentMethodIsCPGWithOrderObject(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->order);

        $this->order->setPaymentMethod(OrderPaymentMethod::OFFLINE)
                    ->setStatus(OrderStatus::WAIT_CUSTOMER);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::CPG);

        self::assertEquals($result, $this->order);
        self::assertEquals(OrderPaymentMethod::CPG, $result->getPaymentMethod());
        self::assertEquals(OrderStatus::WAITING_FOR_PAY, $result->getStatus());
    }

    public function testItCanUpdateOrderPaymentMethodWhenPaymentMethodIsHamrahCardWithOrderObject(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->order);

        $this->order->setPaymentMethod(OrderPaymentMethod::OFFLINE)
                    ->setStatus(OrderStatus::WAIT_CUSTOMER);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::HAMRAH_CARD);

        self::assertEquals($result, $this->order);
        self::assertEquals(OrderPaymentMethod::HAMRAH_CARD, $result->getPaymentMethod());
        self::assertEquals(OrderStatus::WAITING_FOR_PAY, $result->getStatus());
    }

    public function testItCanUpdateOrderPaymentMethodWhenPaymentMethodIsOfflineWithOrderObject(): void
    {
        $this->orderRepoMock->shouldReceive('findUnpaidOrderWithId')
                            ->once()
                            ->with(1)
                            ->andReturn($this->order);

        $this->order->setPaymentMethod(OrderPaymentMethod::ONLINE)
                    ->setStatus(OrderStatus::WAITING_FOR_PAY);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->updateOrderPaymentMethod->perform(1, OrderPaymentMethod::OFFLINE);

        self::assertEquals($result, $this->order);
        self::assertEquals(OrderPaymentMethod::OFFLINE, $result->getPaymentMethod());
        self::assertEquals(OrderStatus::WAIT_CUSTOMER, $result->getStatus());
    }
}
