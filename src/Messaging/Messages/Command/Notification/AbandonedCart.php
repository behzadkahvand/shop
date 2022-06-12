<?php

namespace App\Messaging\Messages\Command\Notification;

class AbandonedCart
{
    public function __construct(private string $cartId)
    {
    }

    public function getCartId(): string
    {
        return $this->cartId;
    }
}
