<?php

namespace App\Tests\Unit\Service\Product\BuyBox;

use App\Messaging\Messages\Command\Product\AddBuyBoxToProduct;
use App\Service\Product\BuyBox\AddBuyBoxService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AddBuyBoxServiceTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MessageBusInterface|MockInterface|null $messageBusMock;

    protected ?AddBuyBoxService $addBuyBoxService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBusMock = Mockery::mock(MessageBusInterface::class);

        $this->addBuyBoxService = new AddBuyBoxService($this->messageBusMock);
    }

    public function testItCanDispatchOneProduct(): void
    {
        $this->messageBusMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(AddBuyBoxToProduct::class))
                             ->andReturn(new Envelope(new stdClass()));

        $this->addBuyBoxService->addOne(1);
    }

    public function testItCanDispatchManyProducts(): void
    {
        $this->messageBusMock->shouldReceive('dispatch')
                             ->times(3)
                             ->with(Mockery::type(AddBuyBoxToProduct::class))
                             ->andReturn(new Envelope(new stdClass()));

        $this->addBuyBoxService->addMany([1, 2, 3]);
    }
}
