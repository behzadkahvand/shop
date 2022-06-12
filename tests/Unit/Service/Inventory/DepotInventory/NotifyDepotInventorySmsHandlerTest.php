<?php

namespace App\Tests\Unit\Service\Inventory\DepotInventory;

use App\Entity\Inventory;
use App\Repository\InventoryRepository;
use App\Service\Inventory\DepotInventory\DepotInventoryMessage;
use App\Service\Inventory\DepotInventory\NotifyDepotInventorySmsHandler;
use App\Service\Notification\DTOs\Seller\NotifyDepotInventorySmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class NotifyDepotInventorySmsHandlerTest extends BaseUnitTestCase
{
    private NotificationService|LegacyMockInterface|MockInterface|null $notificationServiceMock;

    private InventoryRepository|LegacyMockInterface|MockInterface|null $inventoryRepoMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationServiceMock = Mockery::mock(NotificationService::class);
        $this->inventoryRepoMock       = Mockery::mock(InventoryRepository::class);
    }

    public function testSendNotifyViaSmsSuccessfully(): void
    {
        $inventory = Mockery::mock(Inventory::class);

        $this->inventoryRepoMock
            ->shouldReceive('findBy')
            ->andReturn([$inventory, $inventory]);

        $this->notificationServiceMock
            ->shouldNotReceive('send')
            ->twice()
            ->with(Mockery::type(NotifyDepotInventorySmsNotificationDTO::class))
            ->andReturn();

        $handler = new NotifyDepotInventorySmsHandler($this->notificationServiceMock, $this->inventoryRepoMock);

        $handler(new DepotInventoryMessage([10, 20]));
    }
}
