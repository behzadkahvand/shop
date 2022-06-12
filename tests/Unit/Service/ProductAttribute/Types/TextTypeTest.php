<?php

namespace App\Tests\Unit\Service\ProductAttribute\Types;

use App\Entity\ProductAttribute;
use App\Entity\ProductAttributeTextValue;
use App\Service\ProductAttribute\Types\TextType;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class TextTypeTest extends MockeryTestCase
{
    public function testDoesNotSupport()
    {
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getTextValues')
                         ->once()
                         ->withNoArgs()
                         ->andReturn((new ArrayCollection()));

        $listType = new TextType();
        $result   = $listType->supports($productAttribute);

        self::assertFalse($result);
    }

    public function testSupports()
    {
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getTextValues')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(new ArrayCollection([new ProductAttributeTextValue()]));

        $listType = new TextType();
        $result   = $listType->supports($productAttribute);

        self::assertTrue($result);
    }

    public function testGetValue()
    {
        $productAttributeTextValue = Mockery::mock(ProductAttributeTextValue::class);
        $productAttributeTextValue->shouldReceive('getValue')
                                  ->once()
                                  ->withNoArgs()
                                  ->andReturn('test');

        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getTextValues')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(new ArrayCollection([$productAttributeTextValue]));

        $textType = new TextType();
        $result   = $textType->getValue($productAttribute);

        self::assertEquals(['test'], $result);
    }
}
