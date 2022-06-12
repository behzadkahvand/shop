<?php

namespace App\Service\Promotion\Factory;

use App\Entity\Cart;
use App\Entity\CartPromotionDiscount;
use App\Entity\PromotionDiscount;

class CartPromotionDiscountFactory extends AbstractPromotionDiscountFactory
{
    public static function supportedSubjectClass(): string
    {
        return Cart::class;
    }

    protected function initialize(): PromotionDiscount
    {
        return new CartPromotionDiscount();
    }
}
