<?php

namespace App\Service\Cart;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Inventory;
use App\Service\Cart\Condition\CartConditionInterface;
use App\Service\Cart\Exceptions\CartItemNotFoundException;
use App\Service\Cart\Processor\CartProcessorInterface;
use App\Service\Cart\Processor\ContextAwareCartProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final class CartService
{
    protected CartStorageService $cartStorage;

    protected EntityManagerInterface $manager;

    protected CartProcessorInterface $cartProcessor;

    protected CartConditionInterface $condition;

    public function __construct(
        EntityManagerInterface $manager,
        CartStorageService $cartStorage,
        CartProcessorInterface $cartProcessor,
        CartConditionInterface $conditions
    ) {
        $this->manager = $manager;
        $this->cartStorage = $cartStorage;
        $this->cartProcessor = $cartProcessor;
        $this->condition = $conditions;

        $this->disableQueryFilters();
    }

    public function view(array $context = [], Cart $cart = null): Cart
    {
        if ($cart === null) {
            $cart = $this->cartStorage->getCart();
        }

        if (isset($context['address'])) {
            $cart->setAddress($context['address']);
        }

        if ($this->cartProcessor instanceof ContextAwareCartProcessorInterface) {
            $this->cartProcessor->process($cart, $context);
        } else {
            $this->cartProcessor->process($cart);
        }

        $this->manager->flush();

        return $cart;
    }

    /**
     * @throws Throwable
     */
    public function addToCart(Inventory $inventory): Cart
    {
        $cart = $this->cartStorage->getCart();

        $cartItem = $this->getCartItem($cart, $inventory);

        $cartItem = $cartItem === null
            ? $this->createNewCartItem($inventory)
            : $this->increaseCartItemQuantityByOne($cartItem, $inventory);

        $cart->addCartItem($cartItem);

        $this->cartProcessor->process($cart);

        $this->manager->flush();

        return $cart;
    }

    public function remove(Inventory $inventory): Cart
    {
        $cart = $this->cartStorage->findCartOrFail();

        $cartItem = $this->getCartItem($cart, $inventory);

        $this->checkCartItemExistence($cartItem);

        $cart->removeCartItem($cartItem);

        $this->cartProcessor->process($cart);

        $this->manager->flush();

        return $cart;
    }

    /**
     * @throws Throwable
     */
    public function change(Inventory $inventory, int $quantity): Cart
    {
        $cart = $this->cartStorage->findCartOrFail();

        $cartItem = $this->getCartItem($cart, $inventory);

        $this->checkCartItemExistence($cartItem);

        $cartItem = $this->changeCartItemQuantity($cartItem, $inventory, $quantity);

        $cart->addCartItem($cartItem);

        $this->cartProcessor->process($cart);

        $this->manager->flush();

        return $cart;
    }

    public function mergeCarts(Cart $attachedCart, Cart $anonymousCart): void
    {
        $anonymousCartItems = $anonymousCart->getCartItems();
        $attachedCartItems = collect($attachedCart->getCartItems())
            ->groupBy(fn (CartItem $cartItem) => $cartItem->getInventory()->getId())->toArray();

        [$existingItems, $newItems] = $anonymousCartItems
            ->partition(function (int $index, CartItem $cartItem) use ($attachedCartItems) {
                return isset($attachedCartItems[$cartItem->getInventory()->getId()]);
            });

        foreach ($existingItems as $existingItem) {
            [$attachedCartItem] = $attachedCartItems[$existingItem->getInventory()->getId()];
            $attachedCartItem->setQuantity($attachedCartItem->getQuantity() + $existingItem->getQuantity());
        }

        $this->manager->remove($anonymousCart);
        $this->manager->flush();

        foreach ($newItems as $newItem) {
            $attachedCart->addCartItem($newItem);
        }

        $attachedCart->setPromotionCoupon($anonymousCart->getPromotionCoupon());

        $this->manager->flush();
    }

    public function save(Cart $cart = null): ?Cart
    {
        if ($cart === null) {
            $cart = $this->cartStorage->findCartOrFail();
        }

        $this->manager->persist($cart);
        $this->manager->flush();

        return $cart;
    }

    /**
     * @throws Throwable
     */
    private function checkConditions(Inventory $inventory, int $quantity): void
    {
        $this->condition->apply($inventory, $quantity);
    }

    private function getCartItem(Cart $cart, Inventory $inventory): ?CartItem
    {
        $cartItem = $cart->getCartItems()->filter(static function (CartItem $cartItem) use ($inventory) {
            return $cartItem->getInventory()->getId() === $inventory->getId();
        });

        return $cartItem->isEmpty() ? null : $cartItem->first();
    }

    private function checkCartItemExistence(?CartItem $cartItem): void
    {
        if ($cartItem === null) {
            throw new CartItemNotFoundException();
        }
    }

    /**
     * @throws Throwable
     */
    private function createNewCartItem(Inventory $inventory): CartItem
    {
        $this->checkConditions($inventory, 1);

        return (new CartItem())
            ->setInventory($inventory)
            ->setPrice($inventory->getPrice())
            ->setFinalPrice($inventory->getFinalPrice())
            ->setQuantity(1)
            ->setSubtotal($inventory->getPrice() * 1)
            ->setGrandTotal($inventory->getFinalPrice() * 1);
    }

    /**
     * @throws Throwable
     */
    private function increaseCartItemQuantityByOne(CartItem $cartItem, Inventory $inventory): CartItem
    {
        $quantity = $cartItem->getQuantity() + 1;

        $this->checkConditions($inventory, $quantity);

        $cartItem->setQuantity($quantity);

        return $cartItem;
    }

    /**
     * @throws Throwable
     */
    private function changeCartItemQuantity(CartItem $cartItem, Inventory $inventory, int $quantity): CartItem
    {
        $this->checkConditions($inventory, $quantity);

        $cartItem->setQuantity($quantity);

        return $cartItem;
    }

    private function disableQueryFilters(): void
    {
        $filters = [
            'inventoryIsActive',
            'inventoryHasStock',
            'productIsActive',
            'inventoryConfirmedStatus',
            'productWaitingForAcceptStatus',
            'productWithTrashedStatus',
        ];

        foreach ($filters as $filter) {
            if ($this->manager->getFilters()->isEnabled($filter)) {
                $this->manager->getFilters()->disable($filter);
            }
        }
    }
}
