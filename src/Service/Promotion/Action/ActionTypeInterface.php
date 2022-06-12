<?php

namespace App\Service\Promotion\Action;

use App\Entity\Promotion;
use App\Entity\PromotionAction;
use App\Service\Promotion\PromotionSubjectInterface;

interface ActionTypeInterface
{
    public static function getName(): string;

    public function getConfigurationFormType(): string;

    public function apply(PromotionSubjectInterface $subject, PromotionAction $action, Promotion $promotion, array &$context = []): bool;

    public function revert(PromotionSubjectInterface $subject, PromotionAction $action, Promotion $promotion, array &$context = []): bool;
}
