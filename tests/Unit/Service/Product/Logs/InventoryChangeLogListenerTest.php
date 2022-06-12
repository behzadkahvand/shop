<?php

namespace App\Tests\Unit\Service\Product\Logs;

use App\DTO\InventoryLogData;
use App\Entity\Admin;
use App\Entity\Inventory;
use App\Service\Product\Logs\InventoryLogService;
use App\Service\Product\Logs\InventoryChangeLogListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Security\Core\Security;

class InventoryChangeLogListenerTest extends MockeryTestCase
{
    protected $inventoryEntity;
    protected $securityComponentMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->securityComponentMock = Mockery::mock(Security::class);

        $this->inventoryEntity = Mockery::mock(Inventory::class);
        $this->inventoryEntity->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->inventoryEntity = null;
        $this->securityComponentMock = null;
    }

    public function testCanCallOnInventoryPostInsert(): void
    {
        $inventoryLogServiceMock = Mockery::mock(InventoryLogService::class);
        $inventoryLogServiceMock->shouldReceive('dispatchInventoryPriceChangeMessage')
            ->once()
            ->with(1, 0, 0, 1)
            ->andReturn();

        $admin = Mockery::mock(Admin::class);
        $admin->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1);

        $this->securityComponentMock->shouldReceive('getUser')
            ->once()
            ->withNoArgs()
            ->andReturn($admin);

        $listener = new InventoryChangeLogListener($inventoryLogServiceMock, $this->securityComponentMock);

        $listener->onInventoryPostInsert($this->inventoryEntity);
    }

    public function testCanCallOnInventoryPreUpdateWhenOnlyPriceHasChanged(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
            ->once()
            ->with('price')
            ->andReturn(true);

        $args->shouldReceive('getOldValue')
            ->once()
            ->with('price')
            ->andReturn(100);

        $args->shouldReceive('hasChangedField')
            ->once()
            ->with('finalPrice')
            ->andReturn(false);


        $inventoryLogServiceMock = Mockery::mock(InventoryLogService::class);

        $inventoryLogServiceMock->shouldReceive('hasInventoryPriceChanged')
            ->once()
            ->with($args)
            ->andReturn(true);

        $inventoryLogServiceMock->shouldReceive('dispatchInventoryPriceChangeMessage')
            ->once()
            ->with(1, 100, null, null)
            ->andReturn();

        $inventoryLogData = new InventoryLogData(false, []);
        $inventoryLogServiceMock->shouldReceive('checkInventoryIsLoggable')
                                ->once()
                                ->with($this->inventoryEntity, $args)
                                ->andReturn($inventoryLogData);

        $this->securityComponentMock->shouldReceive('getUser')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $listener = new InventoryChangeLogListener($inventoryLogServiceMock, $this->securityComponentMock);

        $listener->onInventoryPreUpdate($this->inventoryEntity, $args);
    }

    public function testInventoryPreUpdateWhenOnlyFinalPriceHasChangedAndInventoryLogHasChanged(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
            ->once()
            ->with('price')
            ->andReturn(false);


        $args->shouldReceive('hasChangedField')
            ->once()
            ->with('finalPrice')
            ->andReturn(true);

        $args->shouldReceive('getOldValue')
            ->once()
            ->with('finalPrice')
            ->andReturn(200);

        $inventoryLogServiceMock = Mockery::mock(InventoryLogService::class);

        $inventoryLogServiceMock->shouldReceive('hasInventoryPriceChanged')
            ->once()
            ->with($args)
            ->andReturn(true);

        $inventoryLogServiceMock->shouldReceive('dispatchInventoryPriceChangeMessage')
            ->once()
            ->with(1, null, 200, null)
            ->andReturn();

        $inventoryLogData = new InventoryLogData(true, []);
        $inventoryLogServiceMock->shouldReceive('checkInventoryIsLoggable')
                                ->once()
                                ->with($this->inventoryEntity, $args)
                                ->andReturn($inventoryLogData);
        $inventoryLogServiceMock->shouldReceive('dispatchInventoryLogMessage')
                                ->once()
                                ->with(Mockery::type(InventoryLogData::class))
                                ->andReturn();

        $admin = Mockery::mock(Admin::class);
        $admin->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(null);

        $this->securityComponentMock->shouldReceive('getUser')
            ->once()
            ->withNoArgs()
            ->andReturn($admin);

        $listener = new InventoryChangeLogListener($inventoryLogServiceMock, $this->securityComponentMock);

        $listener->onInventoryPreUpdate($this->inventoryEntity, $args);
    }

    public function testCanCallOnInventoryPreUpdateWhenPriceAndFinalPriceHaveChanged(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $args->shouldReceive('hasChangedField')
            ->once()
            ->with('price')
            ->andReturn(true);

        $args->shouldReceive('getOldValue')
            ->once()
            ->with('price')
            ->andReturn(100);

        $args->shouldReceive('hasChangedField')
            ->once()
            ->with('finalPrice')
            ->andReturn(true);

        $args->shouldReceive('getOldValue')
            ->once()
            ->with('finalPrice')
            ->andReturn(200);

        $inventoryLogServiceMock = Mockery::mock(InventoryLogService::class);

        $inventoryLogServiceMock->shouldReceive('hasInventoryPriceChanged')
            ->once()
            ->with($args)
            ->andReturn(true);

        $inventoryLogServiceMock->shouldReceive('dispatchInventoryPriceChangeMessage')
            ->once()
            ->with(1, 100, 200, 1)
            ->andReturn();

        $inventoryLogData = new InventoryLogData(false, []);
        $inventoryLogServiceMock->shouldReceive('checkInventoryIsLoggable')
                                ->once()
                                ->with($this->inventoryEntity, $args)
                                ->andReturn($inventoryLogData);

        $admin = Mockery::mock(Admin::class);
        $admin->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1);

        $this->securityComponentMock->shouldReceive('getUser')
            ->once()
            ->withNoArgs()
            ->andReturn($admin);

        $listener = new InventoryChangeLogListener($inventoryLogServiceMock, $this->securityComponentMock);

        $listener->onInventoryPreUpdate($this->inventoryEntity, $args);
    }

    public function testCanCallOnInventoryPreUpdateWhenNoPricesHaveChanged(): void
    {
        $args = Mockery::mock(PreUpdateEventArgs::class);

        $inventoryLogServiceMock = Mockery::mock(InventoryLogService::class);

        $inventoryLogServiceMock->shouldReceive('hasInventoryPriceChanged')
            ->once()
            ->with($args)
            ->andReturn(false);

        $inventoryLogData = new InventoryLogData(false, []);
        $inventoryLogServiceMock->shouldReceive('checkInventoryIsLoggable')
                                ->once()
                                ->with($this->inventoryEntity, $args)
                                ->andReturn($inventoryLogData);

        $this->securityComponentMock->shouldReceive('getUser')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $listener = new InventoryChangeLogListener($inventoryLogServiceMock, $this->securityComponentMock);

        $listener->onInventoryPreUpdate($this->inventoryEntity, $args);
    }
}
