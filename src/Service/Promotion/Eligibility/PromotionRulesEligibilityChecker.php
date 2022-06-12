<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Entity\PromotionRule;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Service\Promotion\Rule\RuleTypeRegistryInterface;

class PromotionRulesEligibilityChecker implements PromotionEligibilityCheckerInterface
{
    private RuleTypeRegistryInterface $ruleTypeRegistry;

    public function __construct(RuleTypeRegistryInterface $ruleTypeRegistry)
    {
        $this->ruleTypeRegistry = $ruleTypeRegistry;
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, Promotion $promotion, array &$context = []): bool
    {
        if ($promotion->getRules()->count() === 0) {
            return true;
        }

        $sortedRules = $this->sortRules($promotion->getRules()->toArray());

        if (count($sortedRules) === 0) {
            throw new \InvalidArgumentException();
        }

        foreach ($sortedRules as $rule) {
            if (!$this->isEligibleToRule($promotionSubject, $rule, $context)) {
                return false;
            }
        }

        return true;
    }

    private function isEligibleToRule(PromotionSubjectInterface $subject, PromotionRule $rule, array &$context = []): bool
    {
        $ruleType = $this->ruleTypeRegistry->get($rule->getType());

        if (!$ruleType) {
            throw new \InvalidArgumentException();
        }

        return $ruleType->isValid($subject, $rule->getConfiguration(), $context);
    }

    private function sortRules(array $rules)
    {
        $ruleTypes = [];
        $ruleTypeIndexes = [];

        foreach ($this->ruleTypeRegistry->getRuleTypeNames() as $index => $ruleTypeName) {
            $ruleTypes[$index] = [];
            $ruleTypeIndexes[$ruleTypeName] = $index;
        }

        foreach ($rules as $rule) {
            if (!isset($ruleTypeIndexes[$rule->getType()])) {
                continue;
            }
            $index = $ruleTypeIndexes[$rule->getType()];
            $ruleTypes[$index][] = $rule;
        }

        $sortedRules = [];
        foreach ($ruleTypes as $rules) {
            array_push($sortedRules, ...$rules);
        }

        return $sortedRules;
    }
}
