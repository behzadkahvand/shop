<?php

namespace App\Service\Builders\Entity;

use App\Entity\Promotion;
use App\Entity\PromotionCoupon;

class PromotionCouponBuilder
{
    private Promotion $promotion;

    public function withPromotion(Promotion $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function build(): PromotionCoupon
    {
        $coupon = new PromotionCoupon();

        if (isset($this->promotion)) {
            $coupon->setPromotion($this->promotion);
        }

        return $coupon;
    }
}
