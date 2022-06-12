<?php

namespace App\Service\Promotion\Eligibility;

use App\Entity\Order;
use App\Entity\PromotionCoupon;
use App\Repository\OrderRepository;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;

final class PromotionCouponPerCustomerUsageLimitEligibilityChecker implements PromotionCouponEligibilityCheckerInterface
{
    private OrderRepository $orderRepository;

    private ContextOperationManager $contextOperationManager;

    public function __construct(OrderRepository $orderRepository, ContextOperationManager $contextOperationManager)
    {
        $this->orderRepository = $orderRepository;
        $this->contextOperationManager = $contextOperationManager;
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, PromotionCoupon $promotionCoupon, array &$context = []): bool
    {
        $perCustomerUsageLimit = $promotionCoupon->getPerCustomerUsageLimit();
        if ($perCustomerUsageLimit === null) {
            return true;
        }

        $customer = $promotionSubject->getCustomer();
        if ($customer === null || $customer->getId() === null) {
            return true;
        }

        $excludedOrder = null;
        if ($promotionSubject instanceof Order) {
            $excludedOrder = $promotionSubject;
        }

        $placedOrdersNumber = $this->orderRepository->countByCustomerAndCoupon(
            $customer,
            $promotionCoupon,
            $excludedOrder
        );

        $valid = $placedOrdersNumber < $perCustomerUsageLimit;

        if (!$valid && $perCustomerUsageLimit === 1) {
            $this->contextOperationManager->addErrorMessage(
                $context,
                'این کد تخفیف تنها یکبار قابل استفاده است.'
            );
        }

        return $valid;
    }
}
