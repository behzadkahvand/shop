<?php

namespace App\Service\Promotion;

class SubjectLockChecker
{
    public function isLocked(PromotionSubjectInterface $promotionSubject): bool
    {
        if (!$promotionSubject instanceof LockablePromotionSubjectInterface) {
            return false;
        }

        return $promotionSubject->isLockedPromotion();
    }
}
