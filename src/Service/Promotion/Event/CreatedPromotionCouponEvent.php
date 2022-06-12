<?php

namespace App\Service\Promotion\Event;

class CreatedPromotionCouponEvent extends AbstractPromotionCouponEvent
{
    public const EVENT_NAME = 'promotion.coupon_created';

    public static function getName()
    {
        return self::EVENT_NAME;
    }
}
