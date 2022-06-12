<?php

namespace App\Service\Apology;

use App\Entity\Apology;
use App\Service\Builders\Entity\PromotionCouponBuilder;
use App\Entity\Customer;
use App\Entity\Promotion;
use App\Service\Promotion\Builders\PromotionCouponDTOBuilder;
use App\Service\Promotion\PromotionCouponService;

class ApologyService
{
    private PromotionCouponBuilder $promotionCouponBuilder;
    private PromotionCouponDTOBuilder $promotionCouponDTOBuilder;
    private PromotionCouponService $promotionCouponService;

    /**
     * ApologyService constructor.
     * @param PromotionCouponService $promotionCouponService
     * @param PromotionCouponBuilder $promotionCouponBuilder
     * @param PromotionCouponDTOBuilder $promotionCouponDTOBuilder
     */
    public function __construct(
        PromotionCouponService $promotionCouponService,
        PromotionCouponBuilder $promotionCouponBuilder,
        PromotionCouponDTOBuilder $promotionCouponDTOBuilder
    ) {
        $this->promotionCouponService = $promotionCouponService;
        $this->promotionCouponBuilder = $promotionCouponBuilder;
        $this->promotionCouponDTOBuilder = $promotionCouponDTOBuilder;
    }

    /**
     * @param Customer[] $customers
     * @param Apology $apology
     */
    public function apologize(array $customers, Apology $apology): void
    {
        $this->promote($customers, $apology->getPromotion());
    }

    /**
     * @param Customer[] $customers
     * @param Promotion $promotion
     */
    protected function promote(array $customers, Promotion $promotion): void
    {
        $coupon = $this->promotionCouponBuilder->withPromotion($promotion)->build();
        $couponDTO = $this->promotionCouponDTOBuilder->withCustomers($customers)->build();

        $this->promotionCouponService->updateFromDTO($coupon, $couponDTO);
    }
}
