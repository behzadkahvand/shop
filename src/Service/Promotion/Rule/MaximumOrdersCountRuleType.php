<?php

namespace App\Service\Promotion\Rule;

use App\Entity\Promotion;
use App\Form\Promotion\RuleConfiguration\MaximumOrdersCountFormType;
use App\Repository\OrderRepository;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;

class MaximumOrdersCountRuleType implements RuleTypeInterface
{
    public const CONFIGURATION_ORDERS_COUNT = 'orders_count';

    private OrderRepository $repository;

    private ContextOperationManager $contextOperationManager;

    public function __construct(OrderRepository $repository, ContextOperationManager $contextOperationManager)
    {
        $this->repository = $repository;
        $this->contextOperationManager = $contextOperationManager;
    }

    public function isValid(PromotionSubjectInterface $promotionSubject, array $configuration, array &$context = []): bool
    {
        if (
            !isset($configuration[self::CONFIGURATION_ORDERS_COUNT]) ||
            !is_int($configuration[self::CONFIGURATION_ORDERS_COUNT])
        ) {
            return false;
        }

        $customer = $promotionSubject->getCustomer();
        if (null === $customer) {
            return false;
        }

        if (null === $customer->getId()) {
            return false;
        }

        $orderCount = $this->repository->countByCustomer(
            $customer,
            $promotionSubject->getId() ? [$promotionSubject->getId()] : []
        );

        $valid = $orderCount <= $configuration[self::CONFIGURATION_ORDERS_COUNT];

        if (!$valid && $configuration[self::CONFIGURATION_ORDERS_COUNT] == 0) {
            $this->contextOperationManager->addErrorMessage(
                $context,
                'این کد تخفیف تنها برای خرید اول قابل استفاده است. '
            );
        }

        return $valid;
    }

    public static function getName(): string
    {
        return 'maximum_orders_count';
    }

    public function getConfigurationFormType(): string
    {
        return MaximumOrdersCountFormType::class;
    }

    public static function getPriority(): int
    {
        return 5;
    }
}
