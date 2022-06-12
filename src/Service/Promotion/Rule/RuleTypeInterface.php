<?php

/**
 * User: amir
 * Date: 11/6/20
 * Time: 12:18 AM
 */

namespace App\Service\Promotion\Rule;

use App\Entity\Promotion;
use App\Service\Promotion\PromotionSubjectInterface;

interface RuleTypeInterface
{
    public function isValid(PromotionSubjectInterface $promotionSubject, array $configuration, array &$context = []): bool;

    public static function getName(): string;

    public function getConfigurationFormType(): string;

    public static function getPriority(): int;
}
