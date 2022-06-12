<?php

namespace App\Tests\Unit\Service\Order\AddBalanceAmount;

use App\Messaging\Messages\Command\Order\AddBalanceAmountToOrder;
use App\Service\Order\AddBalanceAmount\AddBalanceAmountService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AddBalanceAmountServiceTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MessageBusInterface|MockInterface|null $messageBusMock;

    protected ?AddBalanceAmountService $addBalanceAmountService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageBusMock = Mockery::mock(MessageBusInterface::class);

        $this->addBalanceAmountService = new AddBalanceAmountService($this->messageBusMock);
    }

    public function testItCanDispatchOneOrder(): void
    {
        $this->messageBusMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(AddBalanceAmountToOrder::class))
                             ->andReturn(new Envelope(new stdClass()));

        $this->addBalanceAmountService->addOne(1);
    }

    public function testItCanDispatchManyOrders(): void
    {
        $this->messageBusMock->shouldReceive('dispatch')
                             ->times(3)
                             ->with(Mockery::type(AddBalanceAmountToOrder::class))
                             ->andReturn(new Envelope(new stdClass()));

        $this->addBalanceAmountService->addMany([1, 2, 3]);
    }
}
