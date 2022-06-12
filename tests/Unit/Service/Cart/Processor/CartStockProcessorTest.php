<?php

namespace App\Tests\Unit\Service\Cart\Processor;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Service\Cart\Processor\CartStockProcessor;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CartStockProcessorTest extends MockeryTestCase
{
    private Inventory $inventory;

    private Cart $cart;

    private CartItem $cartItem;

    protected function setUp(): void
    {
        $product = (new Product())
            ->setTitle('test');

        $variant = (new ProductVariant())
            ->setProduct($product);

        $this->inventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setMaxPurchasePerOrder(10)
            ->setLeadTime(2)
            ->setVariant($variant)
            ->setSellerStock(10);

        $this->cart = (new Cart())
            ->setSubtotal(0)
            ->setGrandTotal(0);

        $this->cartItem = (new CartItem())
            ->setInventory($this->inventory)
            ->setPrice($this->inventory->getPrice())
            ->setSubtotal($this->inventory->getPrice() * 1)
            ->setGrandTotal($this->inventory->getFinalPrice() * 1);

        $this->cart->addCartItem($this->cartItem);
    }

    protected function tearDown(): void
    {
        unset($this->inventory, $this->cart, $this->cartItem);
    }

    public function testItWontProcessCartIfItemIsNotTrackable(): void
    {
        $this->cartItem->setQuantity(9);

        (new CartStockProcessor())->process($this->cart);

        self::assertEmpty($this->cart->getCartItems()[0]->getMessages());
    }

    public function testItDecreasesQuantityIfItsGreaterThanStock(): void
    {
        $this->inventory->setSellerStock(10);
        $this->cartItem->setQuantity(999);

        (new CartStockProcessor())->process($this->cart);

        self::assertEquals(10, $this->cart->getCartItems()[0]->getQuantity());
        self::assertNotEmpty($this->cart->getCartItems()[0]->getMessages());
    }

    public function testItDecreasesQuantityIfQuantityIsGreaterThanMaxPurchasePerOrder(): void
    {
        $this->inventory->setMaxPurchasePerOrder(10);
        $this->cartItem->setQuantity(999);

        (new CartStockProcessor())->process($this->cart);

        self::assertEquals(10, $this->cart->getCartItems()[0]->getQuantity());
        self::assertNotEmpty($this->cart->getCartItems()[0]->getMessages());
    }

    public function testItRemovesItemFromCartIfItsOutOfStock(): void
    {
        $this->inventory->setSellerStock(0);
        $this->cartItem->setQuantity(1);

        (new CartStockProcessor())->process($this->cart);

        self::assertEquals(0, $this->cart->getCartItems()->count());
    }
}
