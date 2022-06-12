<?php

namespace App\Tests\Unit\Service\Seo;

use App\Messaging\Messages\Command\Seo\AddTitleAndMetaDescription;
use App\Service\Seo\AddTitleAndMetaDescriptionService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AddTitleAndMetaDescriptionServiceTest extends BaseUnitTestCase
{
    private Mockery\LegacyMockInterface|MessageBusInterface|Mockery\MockInterface|null $messageBusMock;

    private ?AddTitleAndMetaDescriptionService $addService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBusMock = Mockery::mock(MessageBusInterface::class);

        $this->addService = new AddTitleAndMetaDescriptionService($this->messageBusMock);
    }

    public function testItCanDispatch(): void
    {
        $this->messageBusMock->shouldReceive('dispatch')
                             ->times(3)
                             ->with(Mockery::type(AddTitleAndMetaDescription::class))
                             ->andReturn(new Envelope(new stdClass()));

        $this->addService->handle([1, 2, 3]);
    }
}
