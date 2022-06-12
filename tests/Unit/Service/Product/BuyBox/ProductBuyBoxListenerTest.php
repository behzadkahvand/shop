<?php

namespace App\Tests\Unit\Service\Product\BuyBox;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Service\Product\BuyBox\AddBuyBoxService;
use App\Service\Product\BuyBox\ProductBuyBoxListener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductBuyBoxListenerTest extends MockeryTestCase
{
    public function testItCanCallAddBuyBox()
    {
        $addBuyBoxMock = Mockery::mock(AddBuyBoxService::class);
        $inventoryMock = Mockery::mock(Inventory::class);
        $variantMock   = Mockery::mock(ProductVariant::class);
        $productMock   = Mockery::mock(Product::class);

        $inventoryMock->shouldReceive('getVariant')
                      ->once()
                      ->withNoArgs()
                      ->andReturn($variantMock);

        $variantMock->shouldReceive('getProduct')
                    ->once()
                    ->withNoArgs()
                    ->andReturn($productMock);

        $productMock->shouldReceive('getId')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(67);

        $addBuyBoxMock->shouldReceive('addOne')
                    ->once()
                    ->with(67)
                    ->andReturn();

        $listener = new ProductBuyBoxListener($addBuyBoxMock);

        $listener->onInventoryPostInsertOrUpdate($inventoryMock);
    }
}
