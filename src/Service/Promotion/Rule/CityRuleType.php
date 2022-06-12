<?php

namespace App\Service\Promotion\Rule;

use App\Entity\Cart;
use App\Entity\Order;
use App\Form\Promotion\RuleConfiguration\CityFormType;
use App\Form\Promotion\RuleConfiguration\ProductFormType;
use App\Service\Promotion\PromotionSubjectInterface;

class CityRuleType implements RuleTypeInterface
{
    public const CONFIGURATION_CITY_IDS = 'city_ids';

    public function isValid(PromotionSubjectInterface $promotionSubject, array $configuration, array &$context = []): bool
    {
        $cityIds = $configuration[self::CONFIGURATION_CITY_IDS];

        $address = $promotionSubject->getAddress();

        if (null === $address) {
            return false;
        }

        return in_array($address->getCity()->getId(), $cityIds);
    }

    public static function getName(): string
    {
        return 'city';
    }

    public function getConfigurationFormType(): string
    {
        return CityFormType::class;
    }

    public static function getPriority(): int
    {
        return 5;
    }
}
