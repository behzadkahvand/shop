<?php

namespace App\Service\Cart\Processor;

use App\Entity\Cart;
use App\Entity\CartItem;

class CartStockProcessor implements CartProcessorInterface
{
    public function process(Cart $cart): void
    {
        /** @var CartItem $cartItem */
        foreach ($cart->getCartItems() as $cartItem) {
            $quantity = $cartItem->getQuantity();
            $stock = $cartItem->getInventory()->getSellerStock();
            $maxPurchasePerOrder = $cartItem->getInventory()->getMaxPurchasePerOrder();

            if ($stock > 0 && ($stock < $quantity || $maxPurchasePerOrder < $quantity)) {
                // Decrease quantity based on `MaxPurchasePerOrder` and `Stock`
                $newQuantity = min($stock, $maxPurchasePerOrder);
                $cartItem->setQuantity($newQuantity);

                $message = $this->getDecreasedStockMessage($cartItem, $newQuantity);
                $cartItem->addMessages($message);
                $cart->addMessages($message);

                continue;
            }

            if ($stock === 0) {
                $cart->addMessages($this->getOutOfStockMessage($cartItem));
                $cart->removeCartItem($cartItem);
            }
        }
    }

    public static function getPriority(): int
    {
        return 110;
    }

    private function getDecreasedStockMessage(CartItem $cartItem, int $quantity): array
    {
        $title = $cartItem->getTitle();

        return [
            'stock_decreased' => [
                'message' => "$title stock has decreased to $quantity",
                'price' => $quantity,
            ],
        ];
    }

    private function getOutOfStockMessage(CartItem $cartItem): array
    {
        $title = $cartItem->getTitle();

        return [
            'out_of_stock' => [
                'message' => "$title is out of stock",
            ],
        ];
    }
}
