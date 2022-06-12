<?php

namespace App\Tests\Unit\Service\Product\Colors;

use App\Messaging\Messages\Command\Product\AddColorsToProduct;
use App\Service\Product\Colors\AddColorsService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AddColorsServiceTest extends BaseUnitTestCase
{
    private Mockery\LegacyMockInterface|MessageBusInterface|Mockery\MockInterface|null $messageBusMock;

    private ?AddColorsService $addBuyBoxService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBusMock = Mockery::mock(MessageBusInterface::class);

        $this->addBuyBoxService = new AddColorsService($this->messageBusMock);
    }

    public function testItCanDispatchOneProduct(): void
    {
        $this->messageBusMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(AddColorsToProduct::class))
                             ->andReturn(new Envelope(new stdClass()));

        $this->addBuyBoxService->add(1);
    }

    public function testItCanDispatchManyProducts(): void
    {
        $this->messageBusMock->shouldReceive('dispatch')
                             ->times(3)
                             ->with(Mockery::type(AddColorsToProduct::class))
                             ->andReturn(new Envelope(new stdClass()));

        $this->addBuyBoxService->batchAdd([1, 2, 3]);
    }
}
