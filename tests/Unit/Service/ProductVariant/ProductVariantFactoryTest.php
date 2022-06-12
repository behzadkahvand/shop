<?php

namespace App\Tests\Unit\Service\ProductVariant;

use App\Entity\Inventory;
use App\Entity\ProductVariant;
use App\Service\ProductVariant\ProductVariantFactory;
use PHPUnit\Framework\TestCase;

class ProductVariantFactoryTest extends TestCase
{
    protected ProductVariantFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ProductVariantFactory();
    }

    public function testItCanGetProductVariant()
    {
        $result = $this->factory->getProductVariant();

        self::assertInstanceOf(ProductVariant::class, $result);
    }

    public function testItCanGetInventory()
    {
        $result = $this->factory->getInventory();

        self::assertInstanceOf(Inventory::class, $result);
    }
}
