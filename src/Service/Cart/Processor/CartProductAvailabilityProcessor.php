<?php

namespace App\Service\Cart\Processor;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Cart;
use App\Entity\CartItem;

class CartProductAvailabilityProcessor implements CartProcessorInterface
{
    public function process(Cart $cart): void
    {
        /** @var CartItem $cartItem */
        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getProductIsActive() && $cartItem->getProductStatus() === ProductStatusDictionary::CONFIRMED) {
                continue;
            }

            $cart->addMessages($this->getProductIsNotActiveMessage($cartItem));
            $cart->removeCartItem($cartItem);
        }
    }

    public static function getPriority(): int
    {
        return 97;
    }

    private function getProductIsNotActiveMessage(CartItem $cartItem): array
    {
        $title = $cartItem->getTitle();

        return [
            'product_is_not_active' => [
                'message' => "$title is not active",
            ],
        ];
    }
}
