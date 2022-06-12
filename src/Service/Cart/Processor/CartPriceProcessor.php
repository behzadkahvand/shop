<?php

namespace App\Service\Cart\Processor;

use App\Entity\Cart;
use App\Entity\CartItem;

class CartPriceProcessor implements CartProcessorInterface
{
    public function process(Cart $cart): void
    {
        /** @var CartItem $cartItem */
        $cartGrandTotal = 0;
        $cartSubTotal = 0;
        foreach ($cart->getCartItems() as $cartItem) {
            $cartItemPrice = $cartItem->getInventory()->getPrice();
            $cartItemQuantity = $cartItem->getQuantity();

            $cartItemSubTotal      = $cartItemPrice * $cartItemQuantity;
            $cartItemGrandTotal    = $cartItem->getInventory()->getFinalPrice() * $cartItemQuantity;

            if ($cartItem->priceHasBeenUpdated()) {
                $cartItem->addMessages($this->getCartItemPriceChangedMessage($cartItem));
                $cart->addMessages($this->getCartPriceChangedMessage($cartItem));
            }

            $cartItem->setPrice($cartItemPrice)
                     ->setFinalPrice($cartItem->getInventory()->getFinalPrice())
                     ->setSubtotal($cartItemSubTotal)
                     ->setGrandTotal($cartItemGrandTotal);

            $cartSubTotal += $cartItem->getSubtotal();
            $cartGrandTotal += $cartItem->getGrandtotal();
        }
        $cart->setSubtotal($cartSubTotal)->setGrandtotal($cartGrandTotal);
    }

    public static function getPriority(): int
    {
        return 105;
    }

    private function getCartItemPriceChangedMessage(CartItem $cartItem): array
    {
        $oldPrice = $cartItem->getPrice();
        $newPrice = $cartItem->getInventory()->getPrice();

        return $newPrice > $oldPrice
            ? [
                'price_increased' => [
                    'message' => "Price has been increased to $newPrice",
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                ],
            ]
            : [
                'price_decreased' => [
                    'message' => "Price has been decreased to $newPrice",
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                ],
            ];
    }

    private function getCartPriceChangedMessage(CartItem $cartItem): array
    {
        $newPrice = $cartItem->getInventory()->getPrice();
        $title = $cartItem->getTitle();

        return [
            'price_changed' => [
                'message' => "Price of $title has been changed to $newPrice",
                'price' => $newPrice,
            ],
        ];
    }
}
