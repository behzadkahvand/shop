<?php

namespace App\DataFixtures;

use App\Entity\PromotionCoupon;
use DateTimeInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PromotionCouponFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'first_coupon',
            $this->createPromotionCoupon(
                'second_fixed_discount_for_first_orders',
                'first_order',
                $this->faker->dateTimeBetween('+10 days', '+15 days'),
                ['customer_4'],
                1,
                1
            )
        );
        $this->setReferenceAndPersist(
            'second_coupon',
            $this->createPromotionCoupon(
                'third_fixed_discount_for_first_orders',
                'second_order',
                $this->faker->dateTimeBetween('+10 days', '+15 days'),
                ['customer_4'],
                1,
                1
            )
        );
        $this->setReferenceAndPersist(
            'third_coupon',
            $this->createPromotionCoupon(
                'fourth_fixed_discount_coupon_only',
                'everyone',
                $this->faker->dateTimeBetween('+10 days', '+15 days'),
                [],
                1,
                1
            )
        );
        $this->setReferenceAndPersist(
            'fourth_coupon',
            $this->createPromotionCoupon(
                'fourth_fixed_discount_coupon_only',
                'everyone_two',
                $this->faker->dateTimeBetween('+10 days', '+15 days'),
                [],
                1,
                1
            )
        );
        $this->setReferenceAndPersist(
            'fifth_coupon',
            $this->createPromotionCoupon(
                'fourth_fixed_discount_coupon_only_2',
                'everyone_three',
                $this->faker->dateTimeBetween('+10 days', '+15 days'),
                [],
                1,
                1
            )
        );

        $this->manager->flush();
    }

    private function createPromotionCoupon(
        string $promotion,
        string $code,
        DateTimeInterface $expireAt,
        array $customers,
        int $perCustomerUsageLimit,
        int $usageLimit,
    ): PromotionCoupon {
        $promotionCoupon = (new PromotionCoupon())
            ->setPromotion($this->getReference($promotion))
            ->setCode($code)
            ->setExpiresAt($expireAt)
            ->setUsageLimit($usageLimit)
            ->setPerCustomerUsageLimit($perCustomerUsageLimit)
            ->setUsageLimit($usageLimit);

        foreach ($customers as $customer) {
            $promotionCoupon->addCustomer($this->getReference($customer));
        }

        return $promotionCoupon;
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            PromotionFixtures::class,
            CustomerFixtures::class,
        ];
    }
}
