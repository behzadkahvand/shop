<?php

namespace App\Tests\Unit\Service\Product\Search\Utils\Doctrine;

use App\Service\Product\Search\Utils\Doctrine\InventoryPriceRangeCalculator;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InventoryPriceRangeCalculatorTest extends BaseUnitTestCase
{
    public function testItCanGetPriceRange(): void
    {
        $queryBuilderMock = Mockery::mock(QueryBuilder::class);
        $queryMock        = Mockery::mock(AbstractQuery::class);

        $priceRangeCalculator = new InventoryPriceRangeCalculator();

        $rootAlias = 'product_0';

        $queryBuilderMock->shouldReceive('getRootAliases')
                         ->once()
                         ->withNoArgs()
                         ->andReturn([$rootAlias]);

        $queryBuilderMock->shouldReceive('innerJoin')
                         ->once()
                         ->with("{$rootAlias}.buyBox", "buyBox")
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('select')
                         ->once()
                         ->with('COALESCE(MIN(buyBox.finalPrice), 0) as min, COALESCE(MAX(buyBox.finalPrice), 0) as max')
                         ->andReturn($queryBuilderMock);
        $queryBuilderMock->shouldReceive('getQuery')
                         ->once()
                         ->withNoArgs()
                         ->andReturn($queryMock);

        $queryMock->shouldReceive('getResult')
                  ->once()
                  ->withNoArgs()
                  ->andReturn([
                      [
                          "min" => "188000",
                          "max" => "460000"
                      ]
                  ]);

        $result = $priceRangeCalculator->getPriceRange($queryBuilderMock, []);


        self::assertEquals([
            "min" => 188000,
            "max" => 460000
        ], $result);
    }
}
