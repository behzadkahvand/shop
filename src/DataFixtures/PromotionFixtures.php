<?php

namespace App\DataFixtures;

use App\Entity\Promotion;
use DateTimeInterface;

class PromotionFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'first_fixed_discount_for_first_orders',
            $this->createPromotion(
                'Fixed Discount For First Orders',
                'Ten Percent Discount For First Orders',
                0,
                10,
                true,
                true,
                $this->faker->dateTimeBetween('-20 minutes'),
                $this->faker->dateTimeBetween('now', '+10 days'),
            )
        );
        $this->setReferenceAndPersist(
            'second_fixed_discount_for_first_orders',
            $this->createPromotion(
                'Second Fixed Discount For First Orders',
                'Second Ten Percent Discount For First Orders',
                0,
                10,
                true,
                true,
                $this->faker->dateTimeBetween('-20 minutes'),
                $this->faker->dateTimeBetween('now', '+10 days'),
            )
        );
        $this->setReferenceAndPersist(
            'third_fixed_discount_for_first_orders',
            $this->createPromotion(
                'Third Fixed Discount For First Orders',
                'Third Ten Percent Discount For First Orders',
                0,
                10,
                true,
                true,
                $this->faker->dateTimeBetween('-20 minutes'),
                $this->faker->dateTimeBetween('now', '+10 days'),
            )
        );
        $this->setReferenceAndPersist(
            'fourth_fixed_discount_coupon_only',
            $this->createPromotion(
                'Fourth Fixed Discount Coupon Based Promotion Without Rule',
                'Fourth Ten Percent Discount For First Orders',
                0,
                10,
                true,
                true,
                $this->faker->dateTimeBetween('-20 minutes'),
                $this->faker->dateTimeBetween('now', '+10 days'),
            )
        );
        $this->setReferenceAndPersist(
            'fourth_fixed_discount_coupon_only_2',
            $this->createPromotion(
                'Fourth Fixed Discount Coupon Based Promotion Without Rule 2',
                'Fourth Ten Percent Discount For First Orders 2',
                0,
                10,
                true,
                true,
                $this->faker->dateTimeBetween('-20 minutes'),
                $this->faker->dateTimeBetween('now', '+10 days'),
            )
        );

        $this->manager->flush();
    }

    private function createPromotion(
        string $name,
        string $description,
        int $priority,
        int $usageLimit,
        bool $couponBased,
        bool $enabled,
        DateTimeInterface $startsAt,
        DateTimeInterface $endsAt
    ): Promotion {
        return (new Promotion())
            ->setName($name)
            ->setDescription($description)
            ->setPriority($priority)
            ->setUsageLimit($usageLimit)
            ->setCouponBased($couponBased)
            ->setEnabled($enabled)
            ->setStartsAt($startsAt)
            ->setEndsAt($endsAt);
    }
}
