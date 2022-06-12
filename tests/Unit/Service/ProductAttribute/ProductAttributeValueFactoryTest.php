<?php

namespace App\Tests\Unit\Service\ProductAttribute;

use App\Dictionary\AttributeTypeDictionary;
use App\Entity\Attribute;
use App\Entity\ProductAttributeNumericValue;
use App\Service\ProductAttribute\Exceptions\AttributeTypeIsNotSupportedException;
use App\Service\ProductAttribute\ProductAttributeValueFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductAttributeValueFactoryTest extends MockeryTestCase
{
    public function testCreate()
    {
        $attribute = \Mockery::mock(Attribute::class);
        $attribute->shouldReceive('getType')
                  ->once()
                  ->withNoArgs()
                  ->andReturn(AttributeTypeDictionary::NUMERIC);

        $result = (new ProductAttributeValueFactory())->create($attribute);

        self::assertInstanceOf(ProductAttributeNumericValue::class, $result);
    }

    public function testCreateThrowException()
    {
        $attribute = \Mockery::mock(Attribute::class);
        $attribute->shouldReceive('getType')
                  ->once()
                  ->withNoArgs()
                  ->andReturn('test');

        self::expectException(AttributeTypeIsNotSupportedException::class);

        (new ProductAttributeValueFactory())->create($attribute);
    }
}
