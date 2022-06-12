<?php

namespace App\Service\Promotion\Factory;

use App\Entity\Order;
use App\Entity\OrderPromotionDiscount;
use App\Entity\PromotionDiscount;

class OrderPromotionDiscountFactory extends AbstractPromotionDiscountFactory
{
    public static function supportedSubjectClass(): string
    {
        return Order::class;
    }

    protected function initialize(): PromotionDiscount
    {
        return new OrderPromotionDiscount();
    }
}
