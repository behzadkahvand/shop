<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\DTO\InventoryPriceHistoryData;
use App\Entity\Inventory;
use App\Messaging\Handlers\Command\Product\LogInventoryPriceChangeHandler;
use App\Messaging\Messages\Command\Product\LogInventoryPriceChange;
use App\Repository\InventoryRepository;
use App\Service\Log\DataLoggerService;
use App\Service\Product\Logs\InventoryLogService;
use App\Service\ProductVariant\Exceptions\InventoryNotFoundException;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class LogInventoryPriceChangeHandlerTest extends BaseUnitTestCase
{
    protected InventoryRepository|LegacyMockInterface|MockInterface|null $inventoryRepoMock;

    protected DataLoggerService|LegacyMockInterface|MockInterface|null $dataLoggerServiceMock;

    protected ?LogInventoryPriceChangeHandler $logInventoryPriceChangeHandler;

    protected ?LogInventoryPriceChange $logInventoryPriceMsgMock;

    protected LegacyMockInterface|MockInterface|InventoryLogService|null $inventoryLogServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryRepoMock       = Mockery::mock(InventoryRepository::class);
        $this->dataLoggerServiceMock   = Mockery::mock(DataLoggerService::class);
        $this->inventoryLogServiceMock = Mockery::mock(InventoryLogService::class);

        $this->logInventoryPriceChangeHandler = new LogInventoryPriceChangeHandler(
            $this->inventoryRepoMock,
            $this->dataLoggerServiceMock,
            $this->inventoryLogServiceMock
        );
    }

    public function testItDoNothingWhenInventoryNotFound(): void
    {
        $this->logInventoryPriceMsgMock = new LogInventoryPriceChange(10, 100, 100, 50);

        $inventoryId = $this->logInventoryPriceMsgMock->getInventoryId();

        $this->inventoryRepoMock->shouldReceive('find')
                                ->once()
                                ->with($inventoryId)
                                ->andReturnNull();

        try {
            $this->logInventoryPriceChangeHandler->__invoke($this->logInventoryPriceMsgMock);
        } catch (\Exception $exception) {
            $this->assertInstanceOf(InventoryNotFoundException::class, $exception);
            $this->assertEquals(sprintf('it is not possible to log inventory price change history %d', 10), $exception->getMessage());
        }
    }


    public function testItDoNothingWhenInventoryFound(): void
    {
        $this->logInventoryPriceMsgMock = new LogInventoryPriceChange(10, 100, 100, 50);

        $inventoryId = $this->logInventoryPriceMsgMock->getInventoryId();

        $inventoryEntityMock = Mockery::mock(Inventory::class);

        $this->inventoryRepoMock->shouldReceive('find')
                                ->once()
                                ->with($inventoryId)
                                ->andReturn($inventoryEntityMock);

        $inventoryPriceDTOMock = new InventoryPriceHistoryData();
        $this->inventoryLogServiceMock->shouldReceive('makeInventoryPriceHistoryDTO')
                                      ->once()
                                      ->with($inventoryEntityMock, $this->logInventoryPriceMsgMock)
                                      ->andReturn($inventoryPriceDTOMock);

        $this->dataLoggerServiceMock->shouldReceive('logInventoryPriceChange')
                                    ->once()
                                    ->with($inventoryPriceDTOMock)
                                    ->andReturn();

        $this->logInventoryPriceChangeHandler->__invoke($this->logInventoryPriceMsgMock);
    }
}
