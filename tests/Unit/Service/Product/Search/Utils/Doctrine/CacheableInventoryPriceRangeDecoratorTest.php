<?php

namespace App\Tests\Unit\Service\Product\Search\Utils\Doctrine;

use App\Service\Product\Search\Utils\Doctrine\CacheableInventoryPriceRangeDecorator;
use App\Service\Product\Search\Utils\Doctrine\InventoryPriceRangeInterface;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheableInventoryPriceRangeDecoratorTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|InventoryPriceRangeInterface|MockInterface|null $decoratedMock;

    protected CacheItemPoolInterface|LegacyMockInterface|MockInterface|null $cacheItemPoolMock;

    protected QueryBuilder|LegacyMockInterface|MockInterface|null $queryBuilderMock;

    protected LegacyMockInterface|MockInterface|CacheItemInterface|null $cacheItemMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->decoratedMock     = Mockery::mock(InventoryPriceRangeInterface::class);
        $this->cacheItemPoolMock = Mockery::mock(CacheItemPoolInterface::class);
        $this->queryBuilderMock  = Mockery::mock(QueryBuilder::class);
        $this->cacheItemMock     = Mockery::mock(CacheItemInterface::class);
    }

    public function testItCanNotSetPriceRangeCacheWhenExpireTimeIsNegative(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];
        $this->decoratedMock->expects('getPriceRange')
                            ->with($this->queryBuilderMock, [])
                            ->andReturns($range);

        $this->cacheItemPoolMock->shouldNotReceive('getItem');

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            -1
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, []);

        self::assertEquals($range, $result);
    }

    public function testItCanNotSetPriceRangeCacheWhenExpireTimeIsZero(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $this->decoratedMock->expects('getPriceRange')
                            ->with($this->queryBuilderMock, [])
                            ->andReturns($range);

        $this->cacheItemPoolMock->shouldNotReceive('getItem');

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            0
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, []);

        self::assertEquals($range, $result);
    }

    public function testItCanNotSetPriceRangeCacheWhenTwoFiltersSet(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $filters = [
            'category.id' => [
                'in' => '1,2,3',
            ],
            'brand.code'  => 'samsung',
        ];

        $this->decoratedMock->expects('getPriceRange')
                            ->with($this->queryBuilderMock, $filters)
                            ->andReturns($range);

        $this->cacheItemPoolMock->shouldNotReceive('getItem');

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, $filters);

        self::assertEquals($range, $result);
    }

    public function testItCanNotSetPriceRangeCacheWhenFilterIsInvalid(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $filters = [
            'isOriginal' => 1,
        ];

        $this->decoratedMock->expects('getPriceRange')
                            ->with($this->queryBuilderMock, $filters)
                            ->andReturns($range);

        $this->cacheItemPoolMock->shouldNotReceive('getItem');

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, $filters);

        self::assertEquals($range, $result);
    }

    public function testItCanNotSetPriceRangeCacheWhenNoFilterSetAndCacheItemIsHit(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $this->decoratedMock->shouldNotReceive('getPriceRange');

        $this->cacheItemPoolMock->expects('getItem')
                                ->with(CacheableInventoryPriceRangeDecorator::CACHE_PREFIX . 'all')
                                ->andReturns($this->cacheItemMock);

        $this->cacheItemMock->expects('isHit')
                            ->withNoArgs()
                            ->andReturnTrue();
        $this->cacheItemMock->expects('get')
                            ->withNoArgs()
                            ->andReturns($range);

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, []);

        self::assertEquals($range, $result);
    }

    public function testItCanSetPriceRangeCacheWhenNoFilterSet(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $this->decoratedMock->expects('getPriceRange')
                            ->with($this->queryBuilderMock, [])
                            ->andReturns($range);

        $this->cacheItemPoolMock->expects('getItem')
                                ->with(CacheableInventoryPriceRangeDecorator::CACHE_PREFIX . 'all')
                                ->andReturns($this->cacheItemMock);
        $this->cacheItemPoolMock->expects('save')
                                ->with($this->cacheItemMock)
                                ->andReturnTrue();

        $this->cacheItemMock->expects('isHit')
                            ->withNoArgs()
                            ->andReturnFalse();
        $this->cacheItemMock->expects('set')
                            ->with($range)
                            ->andReturnSelf();
        $this->cacheItemMock->expects('expiresAfter')
                            ->with(10)
                            ->andReturnSelf();

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, []);

        self::assertEquals($range, $result);
    }

    public function testItCanNotSetPriceRangeCacheWhenCategoryFilterSetAndCacheItemIsHit(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $filters = [
            'category.id' => [
                'in' => '1,2,3',
            ],
        ];

        $this->decoratedMock->shouldNotReceive('getPriceRange');

        $this->cacheItemPoolMock->expects('getItem')
                                ->with(CacheableInventoryPriceRangeDecorator::CACHE_PREFIX . 'category_' . '1,2,3')
                                ->andReturns($this->cacheItemMock);

        $this->cacheItemMock->expects('isHit')
                            ->withNoArgs()
                            ->andReturnTrue();
        $this->cacheItemMock->expects('get')
                            ->withNoArgs()
                            ->andReturns($range);

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, $filters);

        self::assertEquals($range, $result);
    }

    public function testItCanSetPriceRangeCacheWhenCategoryFilterSet(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $filters = [
            'category.id' => [
                'in' => '1,2,3',
            ],
        ];

        $this->decoratedMock->expects('getPriceRange')
                            ->with($this->queryBuilderMock, $filters)
                            ->andReturns($range);

        $this->cacheItemPoolMock->expects('getItem')
                                ->with(CacheableInventoryPriceRangeDecorator::CACHE_PREFIX . 'category_' . '1,2,3')
                                ->andReturns($this->cacheItemMock);
        $this->cacheItemPoolMock->expects('save')
                                ->with($this->cacheItemMock)
                                ->andReturnTrue();

        $this->cacheItemMock->expects('isHit')
                            ->withNoArgs()
                            ->andReturnFalse();
        $this->cacheItemMock->expects('set')
                            ->with($range)
                            ->andReturnSelf();
        $this->cacheItemMock->expects('expiresAfter')
                            ->with(10)
                            ->andReturnSelf();

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, $filters);

        self::assertEquals($range, $result);
    }

    public function testItCanNotSetPriceRangeCacheWhenBrandFilterSetAndCacheItemIsHit(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $filters = [
            'brand.code' => 'samsung',
        ];

        $this->decoratedMock->shouldNotReceive('getPriceRange');

        $this->cacheItemPoolMock->expects('getItem')
                                ->with(CacheableInventoryPriceRangeDecorator::CACHE_PREFIX . 'brand_' . 'samsung')
                                ->andReturns($this->cacheItemMock);

        $this->cacheItemMock->expects('isHit')
                            ->withNoArgs()
                            ->andReturnTrue();
        $this->cacheItemMock->expects('get')
                            ->withNoArgs()
                            ->andReturns($range);

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, $filters);

        self::assertEquals($range, $result);
    }

    public function testItCanSetPriceRangeCacheWhenBrandFilterSet(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $filters = [
            'brand.code' => 'samsung',
        ];

        $this->decoratedMock->expects('getPriceRange')
                            ->with($this->queryBuilderMock, $filters)
                            ->andReturns($range);

        $this->cacheItemPoolMock->expects('getItem')
                                ->with(CacheableInventoryPriceRangeDecorator::CACHE_PREFIX . 'brand_' . 'samsung')
                                ->andReturns($this->cacheItemMock);
        $this->cacheItemPoolMock->expects('save')
                                ->with($this->cacheItemMock)
                                ->andReturnTrue();

        $this->cacheItemMock->expects('isHit')
                            ->withNoArgs()
                            ->andReturnFalse();
        $this->cacheItemMock->expects('set')
                            ->with($range)
                            ->andReturnSelf();
        $this->cacheItemMock->expects('expiresAfter')
                            ->with(10)
                            ->andReturnSelf();

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, $filters);

        self::assertEquals($range, $result);
    }

    public function testItCanNotSetPriceRangeCacheWhenSellerFilterSetAndCacheItemIsHit(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $filters = [
            'productVariants.inventories.seller.identifier' => 'arse',
        ];

        $this->decoratedMock->shouldNotReceive('getPriceRange');

        $this->cacheItemPoolMock->expects('getItem')
                                ->with(CacheableInventoryPriceRangeDecorator::CACHE_PREFIX . 'seller_' . 'arse')
                                ->andReturns($this->cacheItemMock);

        $this->cacheItemMock->expects('isHit')
                            ->withNoArgs()
                            ->andReturnTrue();
        $this->cacheItemMock->expects('get')
                            ->withNoArgs()
                            ->andReturns($range);

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, $filters);

        self::assertEquals($range, $result);
    }

    public function testItCanSetPriceRangeCacheWhenSellerFilterSet(): void
    {
        $range = [
            'min' => 100,
            'max' => 100000,
        ];

        $filters = [
            'productVariants.inventories.seller.identifier' => 'arse',
        ];

        $this->decoratedMock->expects('getPriceRange')
                            ->with($this->queryBuilderMock, $filters)
                            ->andReturns($range);

        $this->cacheItemPoolMock->expects('getItem')
                                ->with(CacheableInventoryPriceRangeDecorator::CACHE_PREFIX . 'seller_' . 'arse')
                                ->andReturns($this->cacheItemMock);
        $this->cacheItemPoolMock->expects('save')
                                ->with($this->cacheItemMock)
                                ->andReturnTrue();

        $this->cacheItemMock->expects('isHit')
                            ->withNoArgs()
                            ->andReturnFalse();
        $this->cacheItemMock->expects('set')
                            ->with($range)
                            ->andReturnSelf();
        $this->cacheItemMock->expects('expiresAfter')
                            ->with(10)
                            ->andReturnSelf();

        $cacheDecorator = new CacheableInventoryPriceRangeDecorator(
            $this->decoratedMock,
            $this->cacheItemPoolMock,
            10
        );

        $result = $cacheDecorator->getPriceRange($this->queryBuilderMock, $filters);

        self::assertEquals($range, $result);
    }
}
