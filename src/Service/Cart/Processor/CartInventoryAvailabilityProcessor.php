<?php

namespace App\Service\Cart\Processor;

use App\Entity\Cart;
use App\Entity\CartItem;

class CartInventoryAvailabilityProcessor implements CartProcessorInterface
{
    public function process(Cart $cart): void
    {
        /** @var CartItem $cartItem */
        foreach ($cart->getCartItems() as $cartItem) {
            $inventory = $cartItem->getInventory();
            if ($inventory->getIsActive() && $inventory->isConfirmed()) {
                continue;
            }

            $cart->addMessages($this->getInventoryIsNotAvailableMessage($cartItem));
            $cart->removeCartItem($cartItem);
        }
    }

    public static function getPriority(): int
    {
        return 98;
    }

    private function getInventoryIsNotAvailableMessage(CartItem $cartItem): array
    {
        $title = $cartItem->getTitle();

        return [
            'inventory_is_not_available' => [
                'message' => "$title is not available",
            ],
        ];
    }
}
