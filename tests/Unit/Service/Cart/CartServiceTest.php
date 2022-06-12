<?php

namespace App\Tests\Unit\Service\Cart;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Customer;
use App\Entity\Inventory;
use App\Service\Cart\CartService;
use App\Service\Cart\CartStorageService;
use App\Service\Cart\Condition\CartConditionInterface;
use App\Service\Cart\Exceptions\CartItemNotFoundException;
use App\Service\Condition\Exceptions\MaxPurchasePerOrderExceededException;
use App\Service\Cart\Processor\CartProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Throwable;

class CartServiceTest extends MockeryTestCase
{
    /**
     * @var EntityManagerInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $manager;

    /**
     * @var CartStorageService|m\LegacyMockInterface|m\MockInterface
     */
    private $cartStorageService;

    /**
     * @var CartProcessorInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $cartProcessor;

    /**
     * @var CartConditionInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $condition;

    private Customer $user;

    private Inventory $inventory;

    private Cart $cart;

    private CartItem $cartItem;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = m::mock(EntityManagerInterface::class);
        $this->manager->shouldReceive('getFilters->isEnabled')->times(6)->andReturnFalse();
        $this->cartStorageService = m::mock(CartStorageService::class);
        $this->cartProcessor = m::mock(CartProcessorInterface::class);
        $this->condition = m::mock(CartConditionInterface::class);

        $this->user = (new Customer())
            ->setMobile('09121234567');

        $this->inventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setMaxPurchasePerOrder(10)
            ->setLeadTime(2);

        $this->cart = (new Cart())
            ->setSubtotal(0)
            ->setGrandTotal(0);

        $this->cartItem = (new CartItem())
            ->setInventory($this->inventory)
            ->setPrice($this->inventory->getPrice())
            ->setQuantity(1)
            ->setSubtotal($this->inventory->getPrice() * 1)
            ->setGrandTotal($this->inventory->getFinalPrice() * 1);

        $this->cartService = new CartService(
            $this->manager,
            $this->cartStorageService,
            $this->cartProcessor,
            $this->condition
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->inventory,
            $this->user,
            $this->cart,
            $this->cartItem,
            $this->cartService
        );

        $this->manager = null;
        $this->cartStorageService = null;
        $this->cartProcessor = null;
        $this->condition = null;
    }

    /**
     * @throws Throwable
     */
    public function testItCanAddItemToAnEmptyCartSuccessfully(): void
    {
        $this->cartStorageService->shouldReceive('getCart')
            ->once()
            ->with()
            ->andReturn($this->cart);

        $this->manager->shouldReceive('flush')
            ->once()
            ->with()
            ->andReturn();

        $quantity = 1;
        $this->condition->shouldReceive('apply')
            ->once()
            ->with($this->inventory, $quantity)
            ->andReturn();

        $this->cartProcessor->shouldReceive('process')
                            ->once()
                            ->with($this->cart)
                            ->andReturn();

        $cart = $this->cartService->addToCart($this->inventory);

        self::assertEquals($cart, $this->cart);
    }

    /**
     * @throws Throwable
     */
    public function testItCanIncreaseQuantityOfAnItemByOneSuccessfully(): void
    {
        $this->cart->addCartItem($this->cartItem);

        $this->cartStorageService->shouldReceive('getCart')
            ->once()
            ->with()
            ->andReturn($this->cart);

        $this->manager->shouldReceive('flush')
            ->once()
            ->with()
            ->andReturn();

        $quantity = $this->cartItem->getQuantity() + 1;
        $this->condition->shouldReceive('apply')
            ->once()
            ->with($this->inventory, $quantity)
            ->andReturn();

        $this->cartProcessor->shouldReceive('process')
                            ->once()
                            ->with($this->cart)
                            ->andReturn();

        $cart = $this->cartService->addToCart($this->inventory);

        self::assertEquals($cart, $this->cart);
    }

    /**
     * @throws Throwable
     */
    public function testItCanChangeQuantityOfAnItemSuccessfully(): void
    {
        $this->cart->addCartItem($this->cartItem);

        $this->cartStorageService->shouldReceive('findCartOrFail')
            ->once()
            ->with()
            ->andReturn($this->cart);

        $this->manager->shouldReceive('flush')
            ->once()
            ->with()
            ->andReturn();

        $quantity = 5;
        $this->condition->shouldReceive('apply')
            ->once()
            ->with($this->inventory, $quantity)
            ->andReturn();

        $this->cartProcessor->shouldReceive('process')
                            ->once()
                            ->with($this->cart)
                            ->andReturn();

        $cart = $this->cartService->change($this->inventory, $quantity);

        self::assertEquals($cart, $this->cart);
    }

    /**
     * @throws Throwable
     */
    public function testItFailsIfCartIsEmptyWhenTryingToChangeQuantityOfAnItem(): void
    {
        $this->expectException(CartItemNotFoundException::class);

        $this->cartStorageService->shouldReceive('findCartOrFail')
            ->once()
            ->with()
            ->andReturn($this->cart);

        $quantity = 1;

        $this->cartService->change($this->inventory, $quantity);
    }

    /**
     * @throws Throwable
     */
    public function testItFailsIfMaxPurchasePerOrderExceededWhenTryingToChangeQuantityOfAnItem(): void
    {
        $this->expectException(MaxPurchasePerOrderExceededException::class);

        $this->cart->addCartItem($this->cartItem);

        $this->cartStorageService->shouldReceive('findCartOrFail')
            ->once()
            ->with()
            ->andReturn($this->cart);

        $quantity = 999;
        $this->condition->shouldReceive('apply')
            ->once()
            ->with($this->inventory, $quantity)
            ->andReturn()
            ->andThrow(new MaxPurchasePerOrderExceededException());

        $this->cartService->change($this->inventory, $quantity);
    }

    /**
     * @throws Throwable
     */
    public function testItCanRemoveAnItemSuccessfully(): void
    {
        $this->cart->addCartItem($this->cartItem);

        $this->cartStorageService->shouldReceive('findCartOrFail')
            ->once()
            ->with()
            ->andReturn($this->cart);

        $this->manager->shouldReceive('flush')
            ->once()
            ->with()
            ->andReturn();

        $this->cartProcessor->shouldReceive('process')
                            ->once()
                            ->with($this->cart)
                            ->andReturn();

        $cart = $this->cartService->remove($this->inventory);

        self::assertEquals($cart, $this->cart);
    }

    /**
     * @throws Throwable
     */
    public function testItFailsIfCartItemNotExistedWhenTryingToRemoveAnItem(): void
    {
        $this->expectException(CartItemNotFoundException::class);

        $this->cartStorageService->shouldReceive('findCartOrFail')
            ->once()
            ->with()
            ->andReturn($this->cart);

        $this->cartService->remove($this->inventory);
    }

    /**
     * @throws Throwable
     */
    public function testItCanViewEmptyCartSuccessfully(): void
    {
        $this->cartStorageService->shouldReceive('getCart')
            ->once()
            ->with()
            ->andReturn($this->cart);

        $this->manager->shouldReceive('flush')
            ->once()
            ->with()
            ->andReturn();

        $this->cartProcessor->shouldReceive('process')
            ->once()
            ->with($this->cart)
            ->andReturn();

        $cart = $this->cartService->view();

        self::assertEquals($cart, $this->cart);
    }

    /**
     * @throws Throwable
     */
    public function testItCanViewUnchangedCartSuccessfully(): void
    {
        $this->cart->addCartItem($this->cartItem);

        $this->cartStorageService->shouldReceive('getCart')
            ->once()
            ->with()
            ->andReturn($this->cart);

        $this->manager->shouldReceive('flush')
            ->once()
            ->with()
            ->andReturn();

        $this->cartProcessor->shouldReceive('process')
            ->once()
            ->with($this->cart)
            ->andReturn();

        $cart = $this->cartService->view();

        self::assertEquals($cart, $this->cart);
    }

    public function testItCanMergeCartsSuccessfully(): void
    {
        $this->manager->shouldReceive('remove')->once()->andReturn();
        $this->manager->shouldReceive('flush')->twice()->andReturn();

        $attachedCart = (new Cart())
            ->setSubtotal(0)
            ->setGrandTotal(0);

        $attachedInventory = (new Inventory())
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setMaxPurchasePerOrder(10)
            ->setLeadTime(10);

        $this->setIdForInventoryMock($attachedInventory, 1);

        $attachedCartItem = (new CartItem())
            ->setInventory($attachedInventory)
            ->setQuantity(1);

        $attachedCart->addCartItem($attachedCartItem);

        $anonymousCart = (new Cart())
            ->setSubtotal(0)
            ->setGrandTotal(0);

        $anonymousInventory = (new Inventory())
            ->setPrice(20)
            ->setFinalPrice(20)
            ->setMaxPurchasePerOrder(20)
            ->setLeadTime(20);

        $this->setIdForInventoryMock($anonymousInventory, 2);

        $anonymousCartItem = (new CartItem())
            ->setInventory($anonymousInventory)
            ->setQuantity(1);

        $anonymousCart->addCartItem($anonymousCartItem);

        $this->cartService->mergeCarts($attachedCart, $anonymousCart);

        self::assertEquals(2, $attachedCart->getCartItems()->count());
    }

    private function setIdForInventoryMock(Inventory $inventory, int $id): void
    {
        (function () use ($id) {
            $this->id = $id;
        })->call($inventory);
    }
}
