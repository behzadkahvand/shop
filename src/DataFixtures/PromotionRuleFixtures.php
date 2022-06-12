<?php

namespace App\DataFixtures;

use App\Entity\PromotionRule;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PromotionRuleFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createPromotionRule(
                'first_fixed_discount_for_first_orders',
                'maximum_orders_count',
                ['orders_count' => 0]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'second_fixed_discount_for_first_orders',
                'maximum_orders_count',
                ['orders_count' => 0]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'third_fixed_discount_for_first_orders',
                'maximum_orders_count',
                ['orders_count' => 0]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'second_fixed_discount_for_first_orders',
                'category',
                ['category_ids' => []]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'third_fixed_discount_for_first_orders',
                'category',
                ['category_ids' => []]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'second_fixed_discount_for_first_orders',
                'product',
                ['product_ids' => []]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'third_fixed_discount_for_first_orders',
                'product',
                ['product_ids' => []]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'second_fixed_discount_for_first_orders',
                'city',
                ['city_ids' => []]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'third_fixed_discount_for_first_orders',
                'city',
                ['city_ids' => []]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'second_fixed_discount_for_first_orders',
                'minimum_basket_total',
                ['basket_total' => 5000]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'third_fixed_discount_for_first_orders',
                'minimum_basket_total',
                ['basket_total' => 5000]
            )
        );
        $this->manager->persist(
            $this->createPromotionRule(
                'fourth_fixed_discount_coupon_only_2',
                'minimum_basket_total',
                ['basket_total' => 6000000]
            )
        );

        $this->manager->flush();
    }

    private function createPromotionRule(
        string $promotion,
        string $type,
        array $configuration,
    ): PromotionRule {
        return (new PromotionRule())
            ->setPromotion($this->getReference($promotion))
            ->setType($type)
            ->setConfiguration($configuration);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            PromotionFixtures::class,
        ];
    }
}
