<?php

namespace App\Tests\Unit\Service\Layout\OnSaleBlock;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Configuration;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Layout\OnSaleBlock\OnSaleInventoryBlock;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Mockery;

class OnSaleInventoryBlockTest extends BaseUnitTestCase
{
    private OnSaleInventoryBlock|null $block;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|ConfigurationServiceInterface|null $configurationServiceMock;
    private Mockery\LegacyMockInterface|ProductRepository|Mockery\MockInterface|null $productRepositoryMock;
    private Mockery\LegacyMockInterface|Product|Mockery\MockInterface|null $productMock;
    private Mockery\LegacyMockInterface|EntityManagerInterface|Mockery\MockInterface|null $entityManagerInterfaceMock;
    private Mockery\LegacyMockInterface|FilterCollection|Mockery\MockInterface|null $filterCollectionMock;

    public function setUp(): void
    {
        $this->configurationServiceMock = Mockery::mock(ConfigurationServiceInterface::class);
        $this->productRepositoryMock = Mockery::mock(ProductRepository::class);
        $this->entityManagerInterfaceMock = Mockery::mock(EntityManagerInterface::class);
        $this->filterCollectionMock = Mockery::mock(FilterCollection::class);
        $this->block = new OnSaleInventoryBlock(
            $this->configurationServiceMock,
            $this->productRepositoryMock,
            $this->entityManagerInterfaceMock
        );
        $this->productMock = Mockery::mock(Product::class);
    }

    public function testItCanGenerateAndReturnProductsByInventoryIds(): void
    {
        $values = [
            ["id" => 3, "priority" => 2],
            ["id" => 1, "priority" => 0],
            ["id" => 2, "priority" => 1]
        ];

        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_INVENTORY)
            ->andReturn((new Configuration())->setValue($values));

        $this->filterCollectionMock->expects('disable')
            ->with('inventoryIsActive')
            ->andReturn();
        $this->filterCollectionMock->expects('disable')
            ->with('inventoryHasStock')
            ->andReturn();
        $this->filterCollectionMock->expects('disable')
            ->with('inventoryConfirmedStatus')
            ->andReturn();

        $this->entityManagerInterfaceMock->shouldReceive('getFilters')
            ->times(3)
            ->andReturn($this->filterCollectionMock);

        $this->productRepositoryMock->shouldReceive('findProductsByInventoryIds')
            ->once()
            ->with([3, 2, 1])
            ->andReturn([$this->productMock, $this->productMock, $this->productMock]);

        $result = $this->block->generate();
        self::assertIsArray($result);
        self::assertCount(3, $result);
    }

    public function testItReturnAnEmptyArrayIfThereIsNoInventory(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_INVENTORY)
            ->andReturn((new Configuration()));
        $result = $this->block->generate();
        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testItReturnAnEmptyArrayIfThereIsNoRecord(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_INVENTORY)
            ->andReturn(null);
        $result = $this->block->generate();
        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testGetCacheSignature(): void
    {
        $values = [
            ["id" => 3, "priority" => 2],
            ["id" => 1, "priority" => 0],
            ["id" => 2, "priority" => 1]
        ];

        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_INVENTORY)
            ->andReturn((new Configuration())->setValue($values));

        self::assertEquals("ON_SALE_INVENTORY_3_2_1", $this->block->getCacheSignature([]));
    }

    public function testGetCacheSignatureIfThereIsNoProduct(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_INVENTORY)
            ->andReturn((new Configuration()));

        self::assertEquals("ON_SALE_INVENTORY_", $this->block->getCacheSignature([]));
    }

    public function testGetCacheSignatureIfThereIsNoRecord(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_INVENTORY)
            ->andReturn(null);

        self::assertEquals("ON_SALE_INVENTORY_", $this->block->getCacheSignature([]));
    }

    public function testGetCacheExpiry(): void
    {
        self::assertEquals(360, $this->block->getCacheExpiry());
    }

    public function testItGetCode(): void
    {
        self::assertEquals('onSaleInventories', $this->block->getCode());
    }

    public function testItGetConfigCode(): void
    {
        self::assertEquals(ConfigurationCodeDictionary::ON_SALE_INVENTORY, $this->block->getConfigCode());
    }
}
