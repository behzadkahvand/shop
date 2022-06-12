<?php

namespace App\Service\Cart\Processor;

use App\Entity\Cart;

interface ContextAwareCartProcessorInterface extends CartProcessorInterface
{
    public function process(Cart $cart, array $context = []): void;
}
