<?php

namespace App\Service\Promotion\Event;

use App\Entity\PromotionCoupon;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

abstract class AbstractPromotionCouponEvent extends GenericEvent
{
    public function __construct($subject = null, array $arguments = [])
    {
        Assert::isInstanceOf($subject, PromotionCoupon::class);

        parent::__construct($subject, $arguments);
    }

    public function getPromotionCoupon(): PromotionCoupon
    {
        return $this->subject;
    }

    abstract public static function getName();
}
