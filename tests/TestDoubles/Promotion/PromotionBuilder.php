<?php

namespace App\Tests\TestDoubles\Promotion;

use App\Entity\Promotion;

class PromotionBuilder
{
    private bool $couponBased = true;
    private int $usageLimit = 1;
    private string $name = 'dummy coupon';
    private int $priority = 1;
    private bool $exclusive = false;
    private bool $enabled = true;

    /**
     * @return Promotion
     */
    public function build(): Promotion
    {
        $promotion = new Promotion();
        $promotion
            ->setCouponBased($this->couponBased)
            ->setUsageLimit($this->usageLimit)
            ->setName($this->name)
            ->setPriority($this->priority)
            ->setExclusive($this->exclusive)
            ->setEnabled($this->enabled);

        return $promotion;
    }
}
