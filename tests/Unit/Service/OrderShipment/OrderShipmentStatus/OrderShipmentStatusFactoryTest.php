<?php

namespace App\Tests\Unit\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusTransitionException;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusFactory;
use App\Service\OrderShipment\OrderShipmentStatus\PreparingOrderShipmentStatus;
use App\Service\OrderShipment\OrderShipmentStatus\WaitingForSendOrderShipmentStatus;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class OrderShipmentStatusFactoryTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|PreparingOrderShipmentStatus|null $preparingOrderShipmentStatusMock;

    protected LegacyMockInterface|WaitingForSendOrderShipmentStatus|MockInterface|null $waitingForSendOrderShipmentStatusMock;

    protected ?OrderShipmentStatusFactory $orderShipmentStatusFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preparingOrderShipmentStatusMock      = Mockery::mock(PreparingOrderShipmentStatus::class);
        $this->waitingForSendOrderShipmentStatusMock = Mockery::mock(WaitingForSendOrderShipmentStatus::class);

        $this->orderShipmentStatusFactory = new OrderShipmentStatusFactory([
            $this->preparingOrderShipmentStatusMock,
            $this->waitingForSendOrderShipmentStatusMock
        ]);
    }

    public function testItThrowsExceptionWhenItDoesNotSupportTheTransition(): void
    {
        $this->preparingOrderShipmentStatusMock->shouldReceive('support')
                                               ->once()
                                               ->with(OrderShipmentStatus::CANCELED)
                                               ->andReturnFalse();
        $this->waitingForSendOrderShipmentStatusMock->shouldReceive('support')
                                                    ->once()
                                                    ->with(OrderShipmentStatus::CANCELED)
                                                    ->andReturnFalse();

        $this->expectException(InvalidOrderShipmentStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order shipment status transition is invalid!');

        $this->orderShipmentStatusFactory->create(OrderShipmentStatus::CANCELED);
    }

    public function testItCanCreateOrderShipmentStatus(): void
    {
        $this->preparingOrderShipmentStatusMock->shouldReceive('support')
                                               ->once()
                                               ->with(OrderShipmentStatus::PREPARING)
                                               ->andReturnTrue();

        $result = $this->orderShipmentStatusFactory->create(OrderShipmentStatus::PREPARING);

        self::assertEquals($result, $this->preparingOrderShipmentStatusMock);
    }
}
