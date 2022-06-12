<?php

namespace App\Service\Cart\Processor;

use App\Entity\Cart;

final class CartProcessorAggregator implements ContextAwareCartProcessorInterface
{
    private iterable $processors;

    public function __construct(iterable $processors)
    {
        $this->processors = $processors;
    }

    public function process(Cart $cart, array $context = []): void
    {
        /** @var CartProcessorInterface $processor */
        foreach ($this->processors as $processor) {
            if ($processor instanceof ContextAwareCartProcessorInterface) {
                $processor->process($cart, $context);
            } else {
                $processor->process($cart);
            }
        }
    }

    public static function getPriority(): int
    {
        return 0;
    }
}
