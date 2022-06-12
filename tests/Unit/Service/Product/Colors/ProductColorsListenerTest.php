<?php

namespace App\Tests\Unit\Service\Product\Colors;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Service\Product\Colors\AddColorsService;
use App\Service\Product\Colors\ProductColorsListener;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class ProductColorsListenerTest extends BaseUnitTestCase
{
    private Mockery\LegacyMockInterface|Inventory|Mockery\MockInterface|null $inventoryMock;
    private Mockery\LegacyMockInterface|Product|Mockery\MockInterface|null $productMock;
    private ?ProductColorsListener $productColorsListener;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|AddColorsService|null $addColorsServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addColorsServiceMock = Mockery::mock(AddColorsService::class);
        $this->inventoryMock = Mockery::mock(Inventory::class);
        $this->productMock = Mockery::mock(Product::class);

        $this->productColorsListener = new ProductColorsListener($this->addColorsServiceMock);
    }

    public function testItCanCallAddColors(): void
    {
        $this->inventoryMock->expects('getProduct')->andReturn($this->productMock);

        $this->productMock->expects('getId')->andReturn(1);

        $this->addColorsServiceMock->expects('add')->with(1)->andReturnNull();

        $this->productColorsListener->onInventoryPostInsertOrUpdate($this->inventoryMock);
    }
}
