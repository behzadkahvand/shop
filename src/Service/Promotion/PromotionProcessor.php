<?php

namespace App\Service\Promotion;

use App\Entity\Promotion;
use App\Service\Promotion\Action\PromotionApplicatorInterface;
use App\Service\Promotion\Eligibility\PromotionEligibilityCheckerInterface;

class PromotionProcessor implements PromotionProcessorInterface
{
    private PromotionApplicatorInterface $promotionApplicator;
    private PromotionEligibilityCheckerInterface $promotionEligibilityChecker;
    private PromotionProviderInterface $promotionProvider;
    private SubjectLockChecker $subjectLockChecker;

    public function __construct(
        PromotionApplicatorInterface $promotionApplicator,
        PromotionEligibilityCheckerInterface $promotionEligibilityChecker,
        PromotionProviderInterface $promotionProvider,
        SubjectLockChecker $subjectLockChecker
    ) {
        $this->promotionApplicator = $promotionApplicator;
        $this->promotionEligibilityChecker = $promotionEligibilityChecker;
        $this->promotionProvider = $promotionProvider;
        $this->subjectLockChecker = $subjectLockChecker;
    }

    public function process(PromotionSubjectInterface $subject, array $context = []): void
    {
        foreach ($subject->getPromotions() as $promotion) {
            $this->promotionApplicator->revert($subject, $promotion, $context);
        }

        $this->applyPromotions($subject, $context);

        if (
            $subject->getPromotionCoupon() &&
            !$subject->getPromotions()->contains($subject->getPromotionCoupon()->getPromotion())
        ) {
            $subject->setPromotionCoupon(null);
        }
    }

    public function processChangedSubject(PromotionSubjectInterface $subject, array $context = []): void
    {
        if ($this->subjectLockChecker->isLocked($subject)) {
            return;
        }

        foreach ($subject->getPromotions() as $promotion) {
            $this->processChangedSubjectForPromotion($subject, $promotion, $context);
        }
    }

    protected function applyPromotions($subject, array $context = [])
    {
        $promotions = $this->promotionProvider->getPromotions($subject);

        foreach ($promotions as $promotion) {
            if (!$promotion->isExclusive()) {
                continue;
            }

            if ($this->applySinglePromotion($subject, $promotion, $context)) {
                return;
            }
        }

        foreach ($promotions as $promotion) {
            if ($promotion->isExclusive()) {
                continue;
            }

            if ($this->promotionEligibilityChecker->isEligible($subject, $promotion, $context)) {
                $this->promotionApplicator->apply($subject, $promotion, $context);
            }
        }
    }

    protected function applySinglePromotion(
        PromotionSubjectInterface $subject,
        Promotion $promotion,
        array $context = []
    ): bool {
        if ($this->promotionEligibilityChecker->isEligible($subject, $promotion, $context)) {
            $this->promotionApplicator->apply($subject, $promotion, $context);

            return true;
        }

        return false;
    }

    protected function processChangedSubjectForPromotion(PromotionSubjectInterface $subject, Promotion $promotion, array $context = [])
    {
        if (!$this->promotionEligibilityChecker->isEligible($subject, $promotion, $context)) {
            $reverted = $this->promotionApplicator->revert($subject, $promotion, $context);

            if ($reverted && $promotion->getCouponBased() && $subject->getPromotionCoupon()->getPromotion()->getId() === $promotion->getId()) {
                $subject->setPromotionCoupon(null);
            }
        }
    }
}
