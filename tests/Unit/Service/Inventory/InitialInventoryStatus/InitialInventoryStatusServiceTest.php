<?php

namespace App\Tests\Unit\Service\Inventory\InitialInventoryStatus;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Dictionary\InventoryStatus;
use App\Entity\Configuration;
use App\Entity\Inventory;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Inventory\InitialInventoryStatus\InitialInventoryStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class InitialInventoryStatusServiceTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|ConfigurationServiceInterface|null $configurationServiceMock;

    protected LegacyMockInterface|Inventory|MockInterface|null $inventoryMock;

    protected Configuration|LegacyMockInterface|MockInterface|null $configurationMock;

    protected ?InitialInventoryStatusService $initialInventoryStatusService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationServiceMock = Mockery::mock(ConfigurationServiceInterface::class);
        $this->inventoryMock            = Mockery::mock(Inventory::class);
        $this->configurationMock        = Mockery::mock(Configuration::class);

        $this->initialInventoryStatusService = new InitialInventoryStatusService($this->configurationServiceMock);
    }

    public function testItCanDoNothingWhenCheckInitialStatusIsNotSet(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                       ->andReturnNull();

        $this->initialInventoryStatusService->set($this->inventoryMock, 1, 10);
    }

    public function testItCanDoNothingWhenCheckInitialStatusIsDisable(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->once()
                                ->withNoArgs()
                                ->andReturnFalse();

        $this->initialInventoryStatusService->set($this->inventoryMock, 1, 10);
    }

    public function testItCanDoNothingOnUpdateSuppliesInToZero(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->once()
                                ->withNoArgs()
                                ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getLeadTime')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(0);
        $this->inventoryMock->shouldReceive('getSellerStock')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(10);

        $this->initialInventoryStatusService->set($this->inventoryMock, 0, 10);
    }

    public function testItCanDoNothingOnUpdateStock(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->once()
                                ->withNoArgs()
                                ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getLeadTime')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(0);
        $this->inventoryMock->shouldReceive('getSellerStock')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(9);

        $this->initialInventoryStatusService->set($this->inventoryMock, 0, 10);
    }

    public function testItCanSetStatusToWaitingForConfirmOnUpdateSuppliesInToZero(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->once()
                                ->withNoArgs()
                                ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getLeadTime')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(0);
        $this->inventoryMock->shouldReceive('setStatus')
                            ->once()
                            ->with(InventoryStatus::WAIT_FOR_CONFIRM)
                            ->andReturn($this->inventoryMock);

        $this->initialInventoryStatusService->set($this->inventoryMock, 1, 10);
    }

    public function testItCanSetStatusToWaitingForConfirmOnUpdateStock(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::CHECK_INITIAL_INVENTORY_STATUS)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->once()
                                ->withNoArgs()
                                ->andReturnTrue();

        $this->inventoryMock->shouldReceive('getLeadTime')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(0);
        $this->inventoryMock->shouldReceive('getSellerStock')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(11);
        $this->inventoryMock->shouldReceive('setStatus')
                            ->once()
                            ->with(InventoryStatus::WAIT_FOR_CONFIRM)
                            ->andReturn($this->inventoryMock);

        $this->initialInventoryStatusService->set($this->inventoryMock, 0, 10);
    }
}
