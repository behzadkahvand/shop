<?php

namespace App\Tests\Unit\Service\Product\Similar;

use App\Entity\Product;
use App\Service\Product\Similar\ExcludeCurrentProductSimilarProductServiceDecorator;
use App\Service\Product\Similar\SimilarProductServiceInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ExcludeCurrentProductSimilarProductServiceDecoratorTest
 */
final class ExcludeCurrentProductSimilarProductServiceDecoratorTest extends MockeryTestCase
{
    public function testExcludeCurrentProductFromSimilarProducts(): void
    {
        $similarProducts = [['id' => 1], ['id' => 2]];

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive(['getId' => 1])->once()->withNoArgs();

        $decorated = \Mockery::mock(SimilarProductServiceInterface::class);
        $decorated->shouldReceive('getSimilarProducts')->once()->with($product)->andReturn($similarProducts);

        $service = new ExcludeCurrentProductSimilarProductServiceDecorator($decorated);

        self::assertSame([['id' => 2]], $service->getSimilarProducts($product));
    }
    public function testExcludeNothingIfCurrentProductDoesNotExistInSimilarProducts(): void
    {
        $similarProducts = [['id' => 1], ['id' => 2]];

        $product = \Mockery::mock(Product::class);
        $product->shouldReceive(['getId' => 3])->once()->withNoArgs();

        $decorated = \Mockery::mock(SimilarProductServiceInterface::class);
        $decorated->shouldReceive('getSimilarProducts')->once()->with($product)->andReturn($similarProducts);

        $service = new ExcludeCurrentProductSimilarProductServiceDecorator($decorated);

        self::assertSame($similarProducts, $service->getSimilarProducts($product));
    }
}
