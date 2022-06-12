<?php

namespace App\Service\Promotion\Event;

class CreatingPromotionCouponEvent extends AbstractPromotionCouponEvent
{
    public const EVENT_NAME = 'promotion.coupon_creating';

    public static function getName()
    {
        return self::EVENT_NAME;
    }
}
