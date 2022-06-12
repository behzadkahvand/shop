<?php

namespace App\Tests\Unit\Service\Product\Similar;

use App\Entity\Product;
use App\Service\Product\Similar\CacheableSimilarProductServiceDecorator;
use App\Service\Product\Similar\SimilarProductServiceInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class CacheableSimilarProductServiceDecoratorTest
 */
final class CacheableSimilarProductServiceDecoratorTest extends MockeryTestCase
{
    public function testGettingSimilarProductsFromCache(): void
    {
        $similarProducts  = [['id' => 2]];

        $decorated = \Mockery::mock(SimilarProductServiceInterface::class);
        $decorated->shouldNotReceive('getSimilarProducts');

        $item = \Mockery::mock(CacheItemInterface::class);
        $item->shouldReceive(['isHit' => true, 'get' => $similarProducts])->once()->withNoArgs();

        $cache = \Mockery::mock(CacheItemPoolInterface::class);
        $cache->shouldReceive('getItem')->once()->with('similar_products_category_1')->andReturn($item);

        $service = new CacheableSimilarProductServiceDecorator($decorated, $cache);

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive('getCategory->getId')->once()->withNoArgs()->andReturn(1);

        self::assertSame($similarProducts, $service->getSimilarProducts($product));
    }

    public function testCachingSimilarProducts(): void
    {
        $similarProducts  = [['id' => 2]];

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive('getCategory->getId')->once()->withNoArgs()->andReturn(1);

        $decorated = \Mockery::mock(SimilarProductServiceInterface::class);
        $decorated->shouldReceive('getSimilarProducts')->once()->with($product)->andReturn($similarProducts);

        $item = \Mockery::mock(CacheItemInterface::class);
        $item->shouldReceive(['isHit' => false])->once()->withNoArgs();
        $item->shouldReceive('set')->once()->with($similarProducts)->andReturn();
        $item->shouldReceive('expiresAfter')->once()->with(15 * 60)->andReturn();

        $cache = \Mockery::mock(CacheItemPoolInterface::class);
        $cache->shouldReceive('getItem')->once()->with('similar_products_category_1')->andReturn($item);
        $cache->shouldReceive('save')->once()->with($item)->andReturn();

        $service = new CacheableSimilarProductServiceDecorator($decorated, $cache);

        self::assertSame($similarProducts, $service->getSimilarProducts($product));
    }
}
