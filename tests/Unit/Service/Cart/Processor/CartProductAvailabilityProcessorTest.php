<?php

namespace App\Tests\Unit\Service\Cart\Processor;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Service\Cart\Processor\CartProductAvailabilityProcessor;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CartProductAvailabilityProcessorTest extends MockeryTestCase
{
    private Product $product;

    private Cart $cart;

    protected function setUp(): void
    {
        $this->product = (new Product())
            ->setTitle('test');

        $variant = (new ProductVariant())
            ->setProduct($this->product);

        $inventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setMaxPurchasePerOrder(10)
            ->setLeadTime(2)
            ->setVariant($variant)
            ->setSellerStock(10)
            ->setIsActive(false);

        $this->cart = (new Cart())
            ->setSubtotal(0)
            ->setGrandTotal(0);

        $cartItem = (new CartItem())
            ->setInventory($inventory)
            ->setPrice($inventory->getPrice())
            ->setSubtotal($inventory->getPrice() * 1)
            ->setGrandTotal($inventory->getFinalPrice() * 1);

        $this->cart->addCartItem($cartItem);
    }

    protected function tearDown(): void
    {
        unset($this->product, $this->cart);
    }

    public function testItRemovesItemFromCartIfProductIsNotActive(): void
    {
        $this->product->setIsActive(false);
        $this->product->setStatus(ProductStatusDictionary::CONFIRMED);

        (new CartProductAvailabilityProcessor())->process($this->cart);

        self::assertEquals(0, $this->cart->getCartItems()->count());
    }

    public function testItRemovesItemFromCartIfProductIsNotConfirmed(): void
    {
        $this->product->setIsActive(true);
        $this->product->setStatus(ProductStatusDictionary::WAITING_FOR_ACCEPT);

        (new CartProductAvailabilityProcessor())->process($this->cart);

        self::assertEquals(0, $this->cart->getCartItems()->count());
    }
}
