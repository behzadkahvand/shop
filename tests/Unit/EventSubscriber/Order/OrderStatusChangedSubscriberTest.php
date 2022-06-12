<?php

namespace App\Tests\Unit\EventSubscriber\Order;

use App\Dictionary\OrderStatus;
use App\Dictionary\TransferReason;
use App\Entity\Order;
use App\EventSubscriber\Order\OrderStatusChangedSubscriber;
use App\Exceptions\Apology\FailedToFindApologyForCancelReasonException;
use App\Service\Order\Apology\OrderCancellationApologyService;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;

class OrderStatusChangedSubscriberTest extends MockeryTestCase
{
    /**
     * @var OrderCancellationApologyService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $apologyService;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|LoggerInterface
     */
    private $logger;

    private OrderStatusChangedSubscriber $sut;

    /**
     * @var OrderStatusChanged|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $event;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $order;
    /**
     * @var \App\Service\Order\Wallet\OrderWalletPaymentHandler|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private Mockery\LegacyMockInterface|Mockery\MockInterface|OrderWalletPaymentHandler|null $walletPaymentHandler;

    public function setUp(): void
    {
        parent::setUp();

        $this->apologyService = Mockery::mock(
            OrderCancellationApologyService::class
        );
        $this->logger         = Mockery::mock(LoggerInterface::class);
        $this->event          = Mockery::mock(OrderStatusChanged::class);
        $this->order          = Mockery::mock(Order::class);
        $this->walletPaymentHandler = Mockery::mock(OrderWalletPaymentHandler::class);
        $this->sut            = new OrderStatusChangedSubscriber(
            $this->apologyService,
            $this->walletPaymentHandler,
            $this->logger
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);
        $this->apologyService = null;
        $this->logger         = null;
        $this->event          = null;
        $this->order          = null;
    }

    public function testOnOrderCanceledShouldApologizeForCancellationAndHandleWalletPayments(): void
    {
        $this->event
            ->shouldReceive('getNewStatus')
            ->once()->andReturn(OrderStatus::CANCELED);

        $this->event
            ->shouldReceive('getOrder')
            ->twice()->andReturn($this->order);

        $this->apologyService
            ->shouldReceive('apologize')
            ->once()->with($this->order)->andReturnNull();

        $this->walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($this->order, TransferReason::ORDER_CANCELED)
            ->andReturnNull();

        $this->sut->onOrderCanceled($this->event);
    }

    public function testOnOrderCanceledShouldNotDoAnythingIfOrderIsNotGettingCanceled(): void
    {
        $this->event
            ->shouldReceive('getNewStatus')
            ->once()->andReturn(OrderStatus::CONFIRMED);

        $this->sut->onOrderCanceled($this->event);
    }

    public function testOnOrderCanceledShouldLogExceptionIfApologyServiceThrowsException(): void
    {
        $this->event
            ->shouldReceive('getNewStatus')
            ->once()->andReturn(OrderStatus::CANCELED);

        $this->event
            ->shouldReceive('getOrder')
            ->times(3)->andReturn($this->order);

        $this->order
            ->shouldReceive('getId')
            ->once()->andReturn(1);

        $this->apologyService
            ->shouldReceive('apologize')
            ->once()->with($this->order)->andThrowExceptions(
                [new FailedToFindApologyForCancelReasonException()]
            );

        $this->logger
            ->shouldReceive('critical')
            ->once()->andReturnNull();

        $this->walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($this->order, TransferReason::ORDER_CANCELED)
            ->andReturnNull();

        $this->sut->onOrderCanceled($this->event);
    }
}
