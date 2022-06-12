<?php

namespace App\Tests\Unit\Service\ProductAttribute\Types;

use App\Entity\AttributeListItem;
use App\Entity\ProductAttribute;
use App\Entity\ProductAttributeListValue;
use App\Service\ProductAttribute\Types\ListType;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ListTypeTest extends MockeryTestCase
{
    public function testDoesNotSupport()
    {
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getListValues')
                         ->once()
                         ->withNoArgs()
                         ->andReturn((new ArrayCollection()));

        $listType = new ListType();
        $result   = $listType->supports($productAttribute);

        self::assertFalse($result);
    }

    public function testSupports()
    {
        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getListValues')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(new ArrayCollection([new ProductAttributeListValue()]));

        $listType = new ListType();
        $result   = $listType->supports($productAttribute);

        self::assertTrue($result);
    }

    public function testGetValue()
    {
        $attributeListItem = Mockery::mock(AttributeListItem::class);
        $attributeListItem->shouldReceive('getTitle')
                          ->once()
                          ->withNoArgs()
                          ->andReturn('test');
        $attributeListItem->shouldReceive('getId')
                          ->once()
                          ->withNoArgs()
                          ->andReturn(1);

        $productAttributeListValue = Mockery::mock(ProductAttributeListValue::class);
        $productAttributeListValue->shouldReceive('getValue')
                                  ->twice()
                                  ->withNoArgs()
                                  ->andReturn($attributeListItem);

        $productAttribute = Mockery::mock(ProductAttribute::class);
        $productAttribute->shouldReceive('getListValues')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(new ArrayCollection([$productAttributeListValue]));

        $listType = new ListType();
        $result   = $listType->getValue($productAttribute);

        self::assertEquals([['id' => 1, 'title' => 'test']], $result);
    }
}
