<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Service\Order\Stages\ChangeOrderStatusStage;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Tests\Unit\BaseUnitTestCase;
use DateTime;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class ChangeOrderStatusStageTest extends BaseUnitTestCase
{
    private Order|LegacyMockInterface|MockInterface|null $orderMock;

    private AbstractPipelinePayload|LegacyMockInterface|MockInterface|null $payloadMock;

    private LegacyMockInterface|CreateOrderStatusLogService|MockInterface|null $createOrderStatusLogMock;

    private ?ChangeOrderStatusStage $sut;

    protected function setUp(): void
    {
        $this->orderMock                = Mockery::mock(Order::class);
        $this->payloadMock              = Mockery::mock(AbstractPipelinePayload::class);
        $this->createOrderStatusLogMock = Mockery::mock(CreateOrderStatusLogService::class);

        $this->sut = new ChangeOrderStatusStage($this->createOrderStatusLogMock);
    }

    public function testGetPriorityAndTag(): void
    {
        self::assertEquals(-30, $this->sut::getPriority());
        self::assertEquals('app.pipeline_stage.order_processing', $this->sut::getTag());
    }

    public function testItCanChangeOrderStatusWhenPaymentMethodIsOffline(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);

        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::OFFLINE);
        $this->orderMock->expects('setStatus')->with(OrderStatus::WAIT_CUSTOMER)->andReturnSelf();
        $this->orderMock->expects('getStatus')->withNoArgs()->andReturns(OrderStatus::NEW);

        $this->createOrderStatusLogMock->expects('perform')
                                       ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false)
                                       ->andReturns();

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangeOrderStatusWhenPaymentMethodIsOnlineAndOrderPayableIsNotZero(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);

        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::ONLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(10000);
        $this->orderMock->expects('setStatus')->with(OrderStatus::WAITING_FOR_PAY)->andReturnSelf();
        $this->orderMock->expects('getStatus')->withNoArgs()->andReturns(OrderStatus::NEW);

        $this->createOrderStatusLogMock->expects('perform')
                                       ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false)
                                       ->andReturns();

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }

    public function testItCanChangeOrderStatusWhenPaymentMethodIsOnlineAndOrderPayableIsZero(): void
    {
        $this->payloadMock->expects('getOrder')->withNoArgs()->andReturns($this->orderMock);

        $this->orderMock->expects('getPaymentMethod')->withNoArgs()->andReturns(OrderPaymentMethod::ONLINE);
        $this->orderMock->expects('getPayable')->withNoArgs()->andReturns(0);
        $this->orderMock->expects('setStatus')->with(OrderStatus::WAIT_CUSTOMER)->andReturnSelf();
        $this->orderMock->expects('setPaidAt')->with(Mockery::type(DateTime::class))->andReturnSelf();
        $this->orderMock->expects('getStatus')->withNoArgs()->andReturns(OrderStatus::NEW);

        $this->createOrderStatusLogMock->expects('perform')
                                       ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false)
                                       ->andReturns();

        $result = $this->sut->__invoke($this->payloadMock);

        self::assertEquals($result, $this->payloadMock);
    }
}
