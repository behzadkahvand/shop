<?php

namespace App\Tests\Unit\Service\ProductAttribute;

use App\Entity\ProductAttribute;
use App\Service\ProductAttribute\ProductAttributeValueService;
use App\Service\ProductAttribute\Types\BooleanType;
use App\Service\ProductAttribute\Types\ListType;
use App\Service\ProductAttribute\Types\NumericType;
use App\Service\ProductAttribute\Types\TextType;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductAttributeValueServiceTest extends MockeryTestCase
{
    public function testGetValue()
    {
        $testValue        = 90;
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $booleanValue     = Mockery::mock(BooleanType::class);
        $booleanValue->shouldReceive('supports')
                     ->once()
                     ->with(Mockery::type(ProductAttribute::class))
                     ->andReturn(false);

        $numericValue = Mockery::mock(NumericType::class);
        $numericValue->shouldReceive('supports')
                     ->once()
                     ->with(Mockery::type(ProductAttribute::class))
                     ->andReturn(true);
        $numericValue->shouldReceive('getValue')
                     ->once()
                     ->with(Mockery::type(ProductAttribute::class))
                     ->andReturn($testValue);

        $productAttributeValueService = new ProductAttributeValueService([$booleanValue, $numericValue]);
        $value                        = $productAttributeValueService->getValue($productAttribute);

        self::assertEquals($value, $testValue);
    }

    public function testGetValueNull()
    {
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $booleanType      = Mockery::mock(BooleanType::class);
        $booleanType->shouldReceive('supports')
                    ->once()
                    ->with(Mockery::type(ProductAttribute::class))
                    ->andReturn(false);

        $numericType = Mockery::mock(NumericType::class);
        $numericType->shouldReceive('supports')
                    ->once()
                    ->with(Mockery::type(ProductAttribute::class))
                    ->andReturn(false);

        $listType = Mockery::mock(ListType::class);
        $listType->shouldReceive('supports')
                 ->once()
                 ->with(Mockery::type(ProductAttribute::class))
                 ->andReturn(false);

        $textType = Mockery::mock(TextType::class);
        $textType->shouldReceive('supports')
                 ->once()
                 ->with(Mockery::type(ProductAttribute::class))
                 ->andReturn(false);

        $productAttributeValueService = new ProductAttributeValueService([
            $booleanType,
            $numericType,
            $listType,
            $textType,
        ]);

        $value = $productAttributeValueService->getValue($productAttribute);

        self::assertNull($value);
    }
}
