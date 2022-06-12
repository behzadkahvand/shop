<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Log;

use App\Document\SmsLog\SmsLog;
use App\Messaging\Handlers\Command\Log\SmsNotificationLogHandler;
use App\Messaging\Messages\Command\Log\SmsNotificationLog;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Messaging\Messages\Command\Notification\SmsNotification;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class SmsNotificationLogHandlerTest extends BaseUnitTestCase
{
    private ?SmsNotificationLogHandler $smsNotificationLogHandler;

    private DocumentManager|LegacyMockInterface|MockInterface|null $documentManagerMock;

    private LegacyMockInterface|SmsNotificationLog|MockInterface|null $smsNotificationLogMock;

    private SmsNotification|LegacyMockInterface|MockInterface|null $smsNotificationMock;

    private LegacyMockInterface|MockInterface|Recipient|null $recipientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->documentManagerMock    = Mockery::mock(DocumentManager::class);
        $this->smsNotificationLogMock = Mockery::mock(SmsNotificationLog::class);
        $this->smsNotificationMock    = Mockery::mock(SmsNotification::class);
        $this->recipientMock          = Mockery::mock(Recipient::class);

        $this->smsNotificationLogHandler = new SmsNotificationLogHandler(
            $this->documentManagerMock,
        );
    }

    public function testItShouldPersistLog(): void
    {
        $this->recipientMock->expects('getMobile')->andReturn('09121111111');
        $this->recipientMock->expects('getName')->andReturn('test');
        $this->recipientMock->expects('getUserId')->andReturn('1');
        $this->recipientMock->expects('getUserType')->andReturn('admin');
        $this->smsNotificationMock->expects('getRecipient')
                                  ->andReturn($this->recipientMock);

        $this->smsNotificationLogMock->expects('getSmsNotification')
                                     ->andReturn($this->smsNotificationMock);

        $this->smsNotificationMock->expects('getMessage')
                                  ->andReturn(Mockery::type('string'));

        $this->smsNotificationMock->expects('getCode')
                                  ->andReturn(Mockery::type('string'));

        $this->smsNotificationMock->expects('getInterface')
                                  ->andReturn(Mockery::type('string'));

        $this->documentManagerMock->shouldReceive('persist')
                                  ->once()
                                  ->with(Mockery::type(SmsLog::class))
                                  ->andReturn();

        $this->documentManagerMock->shouldReceive('flush')
                                  ->andReturn();


        $this->smsNotificationLogHandler->__invoke($this->smsNotificationLogMock);
    }
}
