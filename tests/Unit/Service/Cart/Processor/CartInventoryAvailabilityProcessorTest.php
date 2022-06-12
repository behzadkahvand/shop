<?php

namespace App\Tests\Unit\Service\Cart\Processor;

use App\Dictionary\InventoryStatus;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Service\Cart\Processor\CartInventoryAvailabilityProcessor;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CartInventoryAvailabilityProcessorTest extends MockeryTestCase
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

    public function testItRemovesItemFromCartIfInventoryIsNotActive(): void
    {
        $this->inventory->setIsActive(false);

        (new CartInventoryAvailabilityProcessor())->process($this->cart);

        self::assertEquals(0, $this->cart->getCartItems()->count());

        $cartMessages = $this->cart->getMessages()[0];
        self::assertArrayHasKey('inventory_is_not_available', $cartMessages);

        self::assertEquals(
            sprintf('%s is not available', $this->cartItem->getTitle()),
            $cartMessages['inventory_is_not_available']['message']
        );
    }

    public function testItRemovesItemFromCartIfInventoryIsNotConfirmed(): void
    {
        $this->inventory
            ->setIsActive(true)
            ->setStatus(InventoryStatus::WAIT_FOR_CONFIRM);

        (new CartInventoryAvailabilityProcessor())->process($this->cart);

        self::assertEquals(0, $this->cart->getCartItems()->count());

        $cartMessages = $this->cart->getMessages()[0];
        self::assertArrayHasKey('inventory_is_not_available', $cartMessages);

        self::assertEquals(
            sprintf('%s is not available', $this->cartItem->getTitle()),
            $cartMessages['inventory_is_not_available']['message']
        );
    }
}
