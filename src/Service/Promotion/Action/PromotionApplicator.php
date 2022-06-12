<?php

namespace App\Service\Promotion\Action;

use App\Entity\Promotion;
use App\Service\Promotion\PromotionSubjectInterface;

class PromotionApplicator implements PromotionApplicatorInterface
{
    private ActionTypeRegistryInterface $registry;

    public function __construct(ActionTypeRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function apply(PromotionSubjectInterface $subject, Promotion $promotion, array &$context = []): void
    {
        $applyPromotion = false;
        foreach ($promotion->getActions() as $action) {
            $result = $this->getActionType($action->getType())->apply($subject, $action, $promotion, $context);
            $applyPromotion = $applyPromotion || $result;
        }

        if ($applyPromotion) {
            $subject->addPromotion($promotion);
        }

        $subject->updateTotals();
    }

    public function revert(PromotionSubjectInterface $subject, Promotion $promotion, array &$context = []): bool
    {
        $allReverted = true;
        foreach ($promotion->getActions() as $action) {
            if (!$this->getActionType($action->getType())->revert($subject, $action, $promotion, $context)) {
                $allReverted = false;
            }
        }

        if ($allReverted) {
            $subject->removePromotion($promotion);
        }

        $subject->updateTotals();

        return $allReverted;
    }

    private function getActionType(string $type): ActionTypeInterface
    {
        $actionType = $this->registry->get($type);

        if (!$actionType) {
            throw new \InvalidArgumentException();
        }

        return $actionType;
    }
}
