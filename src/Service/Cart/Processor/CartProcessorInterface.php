<?php

namespace App\Service\Cart\Processor;

use App\Entity\Cart;

interface CartProcessorInterface
{
    /**
     * Process input cart.
     */
    public function process(Cart $cart): void;

    /**
     * Prioritize processors.
     */
    public static function getPriority(): int;
}
