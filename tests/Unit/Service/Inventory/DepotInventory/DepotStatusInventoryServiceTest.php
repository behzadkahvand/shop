<?php

namespace App\Tests\Unit\Service\Inventory\DepotInventory;

use App\Entity\Inventory;
use App\Messaging\Messages\Command\AsyncMessage;
use App\Repository\InventoryRepository;
use App\Service\Inventory\DepotInventory\DepotStatusInventoryService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DepotStatusInventoryServiceTest extends BaseUnitTestCase
{
    private InventoryRepository|LegacyMockInterface|MockInterface|null $inventoryRepoMock;

    private LegacyMockInterface|MessageBusInterface|MockInterface|null $busMock;

    private ?DepotStatusInventoryService $depotStatusService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryRepoMock  = Mockery::mock(InventoryRepository::class);
        $this->busMock            = Mockery::mock(MessageBusInterface::class);
        $this->depotStatusService = new DepotStatusInventoryService($this->inventoryRepoMock, $this->busMock);
    }

    public function testItShouldNotDispatchDepotInventoryMessage(): void
    {
        $orderId = 10;

        $this->inventoryRepoMock
            ->shouldReceive('findDepotInventoryByOrder')
            ->with($orderId)
            ->andReturn([]);

        $this->busMock->shouldNotReceive('dispatch');

        $this->depotStatusService->handle($orderId);
    }

    public function testShouldDispatchDepotInventoryMessage(): void
    {
        $orderId = 10;

        $inventory10 = Mockery::mock(Inventory::class);
        $inventory20 = Mockery::mock(Inventory::class);

        $inventory10
            ->shouldReceive('getId')
            ->andReturn(10);

        $inventory20
            ->shouldReceive('getId')
            ->andReturn(20);

        $this->inventoryRepoMock
            ->shouldReceive('findDepotInventoryByOrder')
            ->with($orderId)
            ->andReturn([
                $inventory10,
                $inventory20,
            ]);

        $this->busMock
            ->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AsyncMessage::class))
            ->andReturn(new Envelope(new \stdClass()));

        $this->depotStatusService->handle($orderId);
    }
}
