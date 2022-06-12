<?php

namespace App\Service\Cart\Processor;

use App\Entity\Cart;
use App\Entity\PromotionDiscount;
use App\Service\Promotion\PromotionProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class CartPromotionProcessor implements ContextAwareCartProcessorInterface
{
    private PromotionProcessorInterface $promotionProcessor;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PromotionProcessorInterface $promotionProcessor,
        EntityManagerInterface $entityManager
    ) {
        $this->promotionProcessor = $promotionProcessor;
        $this->entityManager = $entityManager;
    }

    public function process(Cart $cart, array $context = []): void
    {
        $this->promotionProcessor->processChangedSubject($cart, $context);

        $this->promotionProcessor->process($cart, $context);

        $this->entityManager->flush();
    }

    public static function getPriority(): int
    {
        return 104;
    }
}
