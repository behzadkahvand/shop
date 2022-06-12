<?php

namespace App\Tests\Unit\Service\Notification\SMS;

use App\Entity\Customer;
use App\Service\Notification\SMS\SmsDriverInterface;
use App\Service\Notification\SMS\SmsService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class SmsServiceTest extends MockeryTestCase
{
    /**
     * @var SmsDriverInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $smsDriver;

    private SmsService $smsService;

    private Customer $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->smsDriver = m::mock(SmsDriverInterface::class);

        $this->smsService = new SmsService($this->smsDriver);

        $this->user = new Customer();
        $this->user->setMobile('09121234567');
    }

    protected function tearDown(): void
    {
        unset($this->smsDriver, $this->smsService, $this->user);
    }

    public function testItCanSendSmsSuccessfully(): void
    {
        $this->smsDriver->shouldReceive('sendMessage')
            ->once()
            ->with($this->user->getMobile(), 'TEXT MESSAGE GOES HERE!')
            ->andReturn();

        $this->smsService->sendMessage($this->user->getMobile(), 'TEXT MESSAGE GOES HERE!');
    }
}
