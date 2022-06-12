<?php

namespace App\Tests\Unit\EventSubscriber\Order\ReturnRequest;

use App\Entity\ReturnRequest;
use App\Events\Order\ReturnRequest\ReturnRequestStatusUpdated;
use App\EventSubscriber\Order\ReturnRequest\NotificationOnReturnRequestTransitionsSubscriber;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Order\ReturnRequest\Notification\ReturnRequestNotificationFactory;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class NotificationOnReturnRequestTransitionsSubscriberTest extends BaseUnitTestCase
{
    private NotificationService|LegacyMockInterface|MockInterface|null $notificationService;
    private ReturnRequestNotificationFactory|LegacyMockInterface|MockInterface|null $notificationFactory;
    private NotificationOnReturnRequestTransitionsSubscriber|null $sut;
    private ReturnRequest|LegacyMockInterface|MockInterface|null $returnRequest;
    private LegacyMockInterface|MockInterface|ReturnRequestStatusUpdated|null $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Mockery::mock(ReturnRequestStatusUpdated::class);
        $this->returnRequest = Mockery::mock(ReturnRequest::class);
        $this->notificationService = Mockery::mock(NotificationService::class);
        $this->notificationFactory = Mockery::mock(ReturnRequestNotificationFactory::class);
        $this->sut = new NotificationOnReturnRequestTransitionsSubscriber(
            $this->notificationService,
            $this->notificationFactory
        );
    }

    public function testOnUpdateStatusWhenStatusHasNotification(): void
    {
        $notification = Mockery::mock(AbstractNotificationDTO::class);
        $this->event->shouldReceive('getReturnRequest')->once()->withNoArgs()->andReturn($this->returnRequest);
        $this->notificationFactory->shouldReceive('make')->once()->with($this->returnRequest)->andReturn($notification);
        $this->notificationService->shouldReceive('send')->once()->with($notification)->andReturnNull();

        $this->sut->onUpdateStatus($this->event);
    }

    public function testOnUpdateStatusWhenStatusDoesNotHaveNotification(): void
    {
        $this->event->shouldReceive('getReturnRequest')->once()->withNoArgs()->andReturn($this->returnRequest);
        $this->notificationFactory->shouldReceive('make')->once()->with($this->returnRequest)->andReturnNull();

        $this->sut->onUpdateStatus($this->event);
    }
}
