<?php

namespace App\Tests\Unit\EventSubscriber\Order\ReturnRequest;

use App\Entity\ReturnRequest;
use App\Entity\ReturnRequestItem;
use App\Events\Order\ReturnRequest\ReturnRequestStatusUpdated;
use App\EventSubscriber\Order\ReturnRequest\WorkflowAnnounceSubscriber;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowAnnounceSubscriberTest extends BaseUnitTestCase
{
    private LegacyMockInterface|MockInterface|Event|null $event;
    private ReturnRequest|LegacyMockInterface|MockInterface|null $returnRequest;
    private ReturnRequestItem|null $returnRequestItem;
    private WorkflowAnnounceSubscriber|null $sut;
    private EventDispatcherInterface|LegacyMockInterface|MockInterface|null $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->returnRequest     = Mockery::mock(ReturnRequest::class);
        $this->returnRequestItem = new ReturnRequestItem();
        $this->returnRequestItem->setRequest($this->returnRequest);
        $this->event = Mockery::mock(Event::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->sut = new WorkflowAnnounceSubscriber($this->eventDispatcher);
    }

    public function testOnAfterTransitionWhenRequestStatusChanges(): void
    {
        $this->event
            ->shouldReceive('getSubject')
            ->once()
            ->withNoArgs()
            ->andReturn($this->returnRequestItem);
        $this->returnRequest->shouldReceive('updateStatus')->once()->withNoArgs()->andReturnTrue();

        $this->eventDispatcher->shouldReceive('dispatch')->once()->with(ReturnRequestStatusUpdated::class);

        $this->sut->onAfterTransition($this->event);
    }

    public function testOnAfterTransitionWhenRequestStatusDoesNotChange(): void
    {
        $this->event
            ->shouldReceive('getSubject')
            ->once()
            ->withNoArgs()
            ->andReturn($this->returnRequestItem);
        $this->returnRequest->shouldReceive('updateStatus')->once()->withNoArgs()->andReturnFalse();

        $this->sut->onAfterTransition($this->event);
    }
}
