<?php

namespace App\Service\Promotion;

interface LockablePromotionSubjectInterface extends PromotionSubjectInterface
{
    public function isLockedPromotion(): bool;
}
