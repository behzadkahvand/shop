<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Notification;

use App\Messaging\Handlers\Command\Notification\SmsNotificationHandler;
use App\Messaging\Messages\Command\Log\SmsNotificationLog;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Service\Notification\SMS\SmsService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SmsNotificationHandlerTest extends BaseUnitTestCase
{
    private LegacyMockInterface|SmsService|MockInterface|null $smsServiceMock;

    private LegacyMockInterface|MessageBusInterface|MockInterface|null $messageBusInterfaceMock;

    private ?SmsNotificationHandler $smsNotificationHandler;

    private SmsNotification|LegacyMockInterface|MockInterface|null $smsNotificationMock;

    private LegacyMockInterface|MockInterface|Recipient|null $recipientMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->smsServiceMock          = Mockery::mock(SmsService::class);
        $this->messageBusInterfaceMock = Mockery::mock(MessageBusInterface::class);
        $this->smsNotificationMock     = Mockery::mock(SmsNotification::class);
        $this->recipientMock           = Mockery::mock(Recipient::class);
        $this->smsNotificationHandler  = new SmsNotificationHandler(
            $this->smsServiceMock,
            $this->messageBusInterfaceMock,
        );
    }

    public function testItShouldSendNotification(): void
    {
        $this->recipientMock->expects('getMobile')
                            ->andReturn('09121111111');

        $this->smsNotificationMock->expects('getRecipient')
                                  ->andReturn($this->recipientMock);

        $this->smsNotificationMock->expects('getMessage')
                                  ->andReturn(Mockery::type('string'));

        $this->smsServiceMock->expects('sendMessage');

        $this->messageBusInterfaceMock->shouldReceive('dispatch')
                                      ->with(Mockery::type(SmsNotificationLog::class))
                                      ->andReturn(new Envelope(new stdClass()));

        $this->smsNotificationHandler->__invoke($this->smsNotificationMock);
    }
}
