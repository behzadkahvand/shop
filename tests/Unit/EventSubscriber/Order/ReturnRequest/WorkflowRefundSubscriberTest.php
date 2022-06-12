<?php

namespace App\Tests\Unit\EventSubscriber\Order\ReturnRequest;

use App\Dictionary\TransferReason;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\ReturnRequestItem;
use App\Events\Order\OrderBalanceAmountEvent;
use App\EventSubscriber\Order\ReturnRequest\WorkflowRefundSubscriber;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\Order\ReturnRequest\Transition\ReturnRequestTransition;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowRefundSubscriberTest extends BaseUnitTestCase
{
    private WorkflowRefundSubscriber|null $sut;
    private LegacyMockInterface|MockInterface|Event|null $event;
    private ReturnRequestItem|null $returnRequestItem;
    private LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateOrderDocument;
    private EventDispatcherInterface|LegacyMockInterface|MockInterface|null $dispatcher;
    private LegacyMockInterface|MockInterface|OrderWalletPaymentHandler|null $walletPaymentHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Mockery::mock(Event::class);
        $this->recalculateOrderDocument = Mockery::mock(RecalculateOrderDocument::class);
        $this->walletPaymentHandler = Mockery::mock(OrderWalletPaymentHandler::class);
        $this->dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->returnRequestItem = new ReturnRequestItem();
        $this->sut = new WorkflowRefundSubscriber(
            $this->recalculateOrderDocument,
            $this->walletPaymentHandler,
            $this->dispatcher
        );
    }

    public function testRefundCompleteShouldPerformRefundOperation(): void
    {
        $this->returnRequestItem->setIsReturnable(true);
        $this->event->shouldReceive('getSubject')->once()->withNoArgs()->andReturn($this->returnRequestItem);
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);
        $this->returnRequestItem->setOrderItem($orderItem);

        $this->recalculateOrderDocument->shouldReceive('perform')->once()->with($order)->andReturnNull();

        $this->walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($order, TransferReason::ORDER_REFUND)
            ->andReturnNull();

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(OrderBalanceAmountEvent::class);

        $this->sut->onRefundComplete($this->event);
    }

    public function testShouldReturnSubscribedEventsCorrectly(): void
    {
        $expected = [
            'workflow.return_request.completed.' . ReturnRequestTransition::REFUND => 'onRefundComplete',
        ];

        self::assertEquals($expected, $this->sut::getSubscribedEvents());
    }
}
