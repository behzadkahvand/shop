<?php

namespace App\Tests\Unit\Service\ProductAttribute\Types;

use App\Entity\ProductAttribute;
use App\Entity\ProductAttributeBooleanValue;
use App\Service\ProductAttribute\Types\BooleanType;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class BooleanTypeTest extends MockeryTestCase
{
    public function testDoesNotSupport()
    {
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getBooleanValue')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(null);

        $booleanType = new BooleanType();
        $result      = $booleanType->supports($productAttribute);

        self::assertFalse($result);
    }

    public function testSupports()
    {
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getBooleanValue')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(new ProductAttributeBooleanValue());

        $booleanType = new BooleanType();
        $result      = $booleanType->supports($productAttribute);

        self::assertTrue($result);
    }

    public function testGetValue()
    {
        $productAttributeBooleanValue = Mockery::mock(ProductAttributeBooleanValue::class);
        $productAttributeBooleanValue->shouldReceive('getValue')
                                     ->once()
                                     ->withNoArgs()
                                     ->andReturn(true);

        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getBooleanValue')
                         ->once()
                         ->withNoArgs()
                         ->andReturn($productAttributeBooleanValue);

        $booleanType = new BooleanType();
        $result      = $booleanType->getValue($productAttribute);

        self::assertTrue($result);
    }
}
