<?php

namespace App\Tests\Unit\Service\ProductAttribute\Types;

use App\Entity\ProductAttribute;
use App\Entity\ProductAttributeNumericValue;
use App\Service\ProductAttribute\Types\NumericType;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class NumericTypeTest extends MockeryTestCase
{
    public function testDoesNotSupport()
    {
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getNumericValue')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(null);

        $numericType = new NumericType();
        $result      = $numericType->supports($productAttribute);

        self::assertFalse($result);
    }

    public function testSupports()
    {
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getNumericValue')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(new ProductAttributeNumericValue());

        $numericType = new NumericType();
        $result      = $numericType->supports($productAttribute);

        self::assertTrue($result);
    }

    public function testGetValue()
    {
        $testValue                    = 10;
        $productAttributeNumericValue = Mockery::mock(ProductAttributeNumericValue::class);
        $productAttributeNumericValue->shouldReceive('getValue')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturn($testValue);

        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getNumericValue')
                         ->once()
                         ->withNoArgs()
                         ->andReturn($productAttributeNumericValue);

        $numericType = new NumericType();
        $result      = $numericType->getValue($productAttribute);

        self::assertEquals($result, $testValue);
    }
}
