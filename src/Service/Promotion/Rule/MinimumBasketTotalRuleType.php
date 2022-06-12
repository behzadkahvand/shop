<?php

namespace App\Service\Promotion\Rule;

use App\Entity\CartItem;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\Promotion\RuleConfiguration\MinimumBasketTotalFormType;
use App\Form\Promotion\RuleConfiguration\ProductFormType;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\ORM\EntityManagerInterface;

class MinimumBasketTotalRuleType implements RuleTypeInterface
{
    private EntityManagerInterface $entityManager;
    private ContextOperationManager $contextOperationManager;

    public function __construct(EntityManagerInterface $entityManager, ContextOperationManager $contextOperationManager)
    {
        $this->entityManager = $entityManager;
        $this->contextOperationManager = $contextOperationManager;
    }

    public const CONFIGURATION_BASKET_TOTAL = 'basket_total';

    public function isValid(PromotionSubjectInterface $promotionSubject, array $configuration, array &$context = []): bool
    {
        if (
            !isset($configuration[self::CONFIGURATION_BASKET_TOTAL]) ||
            !is_int($configuration[self::CONFIGURATION_BASKET_TOTAL])
        ) {
            return false;
        }

        if (
            isset($context['inventory_ids']) &&
            is_array($context['inventory_ids']) &&
            count($context['inventory_ids']) > 0
        ) {
            $total = 0;
            $promotionSubject->getItems()->forAll(function ($key, $item) use (&$total, $context) {
                if (in_array($item->getInventory()->getId(), $context['inventory_ids'])) {
                    $total += $item->getGrandTotal();
                }

                return true;
            });
        } else {
            $total = $promotionSubject->getPromotionSubjectTotal();
        }

        $valid = $total >= $configuration[self::CONFIGURATION_BASKET_TOTAL];

        if (!$valid) {
            $this->contextOperationManager->addErrorMessage(
                $context,
                'حداقل میزان سبد خرید رعایت نشده است. '
            );
        }

        return $valid;
    }

    public static function getName(): string
    {
        return 'minimum_basket_total';
    }

    public function getConfigurationFormType(): string
    {
        return MinimumBasketTotalFormType::class;
    }

    public static function getPriority(): int
    {
        return 0;
    }
}
