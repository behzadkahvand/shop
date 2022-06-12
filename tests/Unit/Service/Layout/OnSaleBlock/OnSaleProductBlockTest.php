<?php

namespace App\Tests\Unit\Service\Layout\OnSaleBlock;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Configuration;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Layout\OnSaleBlock\OnSaleProductBlock;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;

class OnSaleProductBlockTest extends BaseUnitTestCase
{
    private OnSaleProductBlock|null $block;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|ConfigurationServiceInterface|null $configurationServiceMock;
    private Mockery\LegacyMockInterface|ProductRepository|Mockery\MockInterface|null $productRepositoryMock;
    private Mockery\LegacyMockInterface|Product|Mockery\MockInterface|null $productMock;
    private Mockery\LegacyMockInterface|EntityManagerInterface|Mockery\MockInterface|null $entityManagerInterfaceMock;

    public function setUp(): void
    {
        $this->configurationServiceMock = Mockery::mock(ConfigurationServiceInterface::class);
        $this->productRepositoryMock = Mockery::mock(ProductRepository::class);
        $this->entityManagerInterfaceMock = Mockery::mock(EntityManagerInterface::class);
        $this->block = new OnSaleProductBlock(
            $this->configurationServiceMock,
            $this->productRepositoryMock,
            $this->entityManagerInterfaceMock
        );
        $this->productMock = Mockery::mock(Product::class);
    }

    public function testItCanGenerateAndReturnProductsByProductIds(): void
    {
        $values = [
            ["id" => 3, "priority" => 2],
            ["id" => 1, "priority" => 0],
            ["id" => 2, "priority" => 1]
        ];

        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_PRODUCTS)
            ->andReturn((new Configuration())->setValue($values));
        $this->productRepositoryMock->shouldReceive('listByIds')
            ->once()
            ->with([3, 2, 1])
            ->andReturn([$this->productMock, $this->productMock, $this->productMock]);

        $result = $this->block->generate();
        self::assertIsArray($result);
        self::assertCount(3, $result);
    }

    public function testItReturnAnEmptyArrayIfThereIsNoProduct(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_PRODUCTS)
            ->andReturn((new Configuration()));
        $result = $this->block->generate();
        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testItReturnAnEmptyArrayIfThereIsNoRecord(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_PRODUCTS)
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
            ->with(ConfigurationCodeDictionary::ON_SALE_PRODUCTS)
            ->andReturn((new Configuration())->setValue($values));

        self::assertEquals("ON_SALE_PRODUCTS_3_2_1", $this->block->getCacheSignature([]));
    }

    public function testGetCacheSignatureIfThereIsNoProduct(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_PRODUCTS)
            ->andReturn((new Configuration()));

        self::assertEquals("ON_SALE_PRODUCTS_", $this->block->getCacheSignature([]));
    }

    public function testGetCacheSignatureIfThereIsNoRecord(): void
    {
        $this->configurationServiceMock->shouldReceive('findByCode')
            ->once()
            ->with(ConfigurationCodeDictionary::ON_SALE_PRODUCTS)
            ->andReturn(null);

        self::assertEquals("ON_SALE_PRODUCTS_", $this->block->getCacheSignature([]));
    }

    public function testGetCacheExpiry(): void
    {
        self::assertEquals(360, $this->block->getCacheExpiry());
    }

    public function testGetCode(): void
    {
        self::assertEquals('onSaleProducts', $this->block->getCode());
    }

    public function testGetConfigCode(): void
    {
        self::assertEquals(ConfigurationCodeDictionary::ON_SALE_PRODUCTS, $this->block->getConfigCode());
    }
}
