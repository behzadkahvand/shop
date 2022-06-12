<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Product;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Messaging\Handlers\Command\Product\AddBuyBoxToProductHandler;
use App\Messaging\Messages\Command\Product\AddBuyBoxToProduct;
use App\Repository\InventoryRepository;
use App\Service\Product\BuyBox\BuyBoxValidatorService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;

class AddBuyBoxToProductHandlerTest extends BaseUnitTestCase
{
    protected InventoryRepository|LegacyMockInterface|MockInterface|null $inventoryRepoMock;

    protected LegacyMockInterface|MockInterface|BuyBoxValidatorService|null $buyBoxValidatorMock;

    protected LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;

    protected LoggerInterface|LegacyMockInterface|MockInterface|null $loggerMock;

    protected LegacyMockInterface|Product|MockInterface|null $productMock;

    protected LegacyMockInterface|Inventory|MockInterface|null $inventoryMock;

    protected ?AddBuyBoxToProductHandler $addBuyBoxToProductHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventoryRepoMock   = Mockery::mock(InventoryRepository::class);
        $this->buyBoxValidatorMock = Mockery::mock(BuyBoxValidatorService::class);
        $this->em                  = Mockery::mock(EntityManagerInterface::class);
        $this->loggerMock          = Mockery::mock(LoggerInterface::class);
        $this->productMock         = Mockery::mock(Product::class);
        $this->inventoryMock       = Mockery::mock(Inventory::class);

        $this->addBuyBoxToProductHandler = new AddBuyBoxToProductHandler(
            $this->inventoryRepoMock,
            $this->buyBoxValidatorMock,
            $this->em
        );
    }

    public function testItDoNothingWhenProductNotFound(): void
    {
        $productId          = 34;
        $addBuyBoxToProduct = new AddBuyBoxToProduct($productId);

        $this->em->shouldReceive('getReference')
                 ->once()
                 ->with(Product::class, $productId)
                 ->andReturnNull();

        $this->addBuyBoxToProductHandler->setLogger($this->loggerMock);

        $this->loggerMock->shouldReceive('error')
                         ->once()
                         ->with(sprintf('It can not add buy box to product %d when product not exist!', $productId))
                         ->andReturn();

        ($this->addBuyBoxToProductHandler)($addBuyBoxToProduct);
    }

    public function testItDoNothingWhenBuyBoxNotFound(): void
    {
        $productId          = 34;
        $addBuyBoxToProduct = new AddBuyBoxToProduct($productId);

        $this->em->shouldReceive('getReference')
                 ->once()
                 ->with(Product::class, $productId)
                 ->andReturn($this->productMock);

        $this->inventoryRepoMock->shouldReceive('getAvailableInventoriesByProductId')
                                ->once()
                                ->with($productId)
                                ->andReturn([]);

        $this->addBuyBoxToProductHandler->setLogger($this->loggerMock);

        $this->loggerMock->shouldReceive('error')
                         ->once()
                         ->with(sprintf('It can not add buy box to product %d when buy box not exist!', $productId))
                         ->andReturn();

        ($this->addBuyBoxToProductHandler)($addBuyBoxToProduct);
    }

    public function testItCanNotAddBuyBoxToProductWhenBuyBoxIsInvalid(): void
    {
        $productId          = 34;
        $addBuyBoxToProduct = new AddBuyBoxToProduct($productId);

        $this->em->shouldReceive('getReference')
                 ->once()
                 ->with(Product::class, $productId)
                 ->andReturn($this->productMock);

        $this->inventoryRepoMock->shouldReceive('getAvailableInventoriesByProductId')
                                ->once()
                                ->with($productId)
                                ->andReturn([
                                    $this->inventoryMock,
                                    $this->inventoryMock,
                                    $this->inventoryMock,
                                    $this->inventoryMock
                                ]);

        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->times(12)
                            ->withNoArgs()
                            ->andReturn(30000, 31000, 31000, 29000, 30000, 29000, 31000, 28550, 30000, 28550, 29000, 28550);

        $this->buyBoxValidatorMock->shouldReceive('validate')
                                  ->once()
                                  ->with($this->productMock, $this->inventoryMock)
                                  ->andReturnFalse();

        ($this->addBuyBoxToProductHandler)($addBuyBoxToProduct);
    }

    public function testItCanAddBuyBoxToProduct(): void
    {
        $productId          = 34;
        $addBuyBoxToProduct = new AddBuyBoxToProduct($productId);

        $this->em->shouldReceive('getReference')
                 ->once()
                 ->with(Product::class, $productId)
                 ->andReturn($this->productMock);

        $this->inventoryRepoMock->shouldReceive('getAvailableInventoriesByProductId')
                                ->once()
                                ->with($productId)
                                ->andReturn([
                                    $this->inventoryMock,
                                    $this->inventoryMock,
                                    $this->inventoryMock,
                                    $this->inventoryMock
                                ]);

        $this->inventoryMock->shouldReceive('getFinalPrice')
                            ->times(12)
                            ->withNoArgs()
                            ->andReturn(30000, 31000, 31000, 29000, 30000, 29000, 31000, 28550, 30000, 28550, 29000, 28550);

        $this->buyBoxValidatorMock->shouldReceive('validate')
                                  ->once()
                                  ->with($this->productMock, $this->inventoryMock)
                                  ->andReturnTrue();

        $this->productMock->shouldReceive('setBuyBox')
                          ->once()
                          ->with($this->inventoryMock)
                          ->andReturn($this->productMock);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        ($this->addBuyBoxToProductHandler)($addBuyBoxToProduct);
    }
}
