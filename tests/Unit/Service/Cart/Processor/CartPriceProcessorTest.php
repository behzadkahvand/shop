<?php

namespace App\Tests\Unit\Service\Cart\Processor;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Service\Cart\Processor\CartPriceProcessor;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CartPriceProcessorTest extends MockeryTestCase
{
    private Cart $cart;

    protected function setUp(): void
    {
        $product = (new Product())
            ->setTitle('test');

        $variant = (new ProductVariant())
            ->setProduct($product);

        $inventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setMaxPurchasePerOrder(10)
            ->setLeadTime(2)
            ->setVariant($variant);

        $this->cart = (new Cart())
            ->setSubtotal(0)
            ->setGrandTotal(0);

        $cartItem = (new CartItem())
            ->setInventory($inventory)
            ->setPrice(999)
            ->setQuantity(1)
            ->setSubtotal($inventory->getPrice() * 1)
            ->setGrandTotal($inventory->getFinalPrice() * 1);

        $this->cart->addCartItem($cartItem);
    }

    protected function tearDown(): void
    {
        unset($this->cart);
    }

    public function testItCanProcessCartIfPriceIsUpdated(): void
    {
        (new CartPriceProcessor())->process($this->cart);

        self::assertNotEmpty($this->cart->getCartItems()[0]->getMessages());
    }
}
