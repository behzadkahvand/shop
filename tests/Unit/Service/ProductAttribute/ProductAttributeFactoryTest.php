<?php

namespace App\Tests\Unit\Service\ProductAttribute;

use App\Entity\ProductAttribute;
use App\Service\ProductAttribute\ProductAttributeFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductAttributeFactoryTest extends MockeryTestCase
{
    public function testCreate(): void
    {
        $result = (new ProductAttributeFactory())->create();

        self::assertInstanceOf(ProductAttribute::class, $result);
    }
}
