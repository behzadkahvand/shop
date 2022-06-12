<?php

namespace App\DataFixtures;

use App\Entity\PromotionAction;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PromotionActionFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'fixed_discount_1',
            $this->createPromotionAction(
                'first_fixed_discount_for_first_orders',
                'fixed_discount',
                ['amount' => 1000],
            )
        );
        $this->setReferenceAndPersist(
            'fixed_discount_2',
            $this->createPromotionAction(
                'second_fixed_discount_for_first_orders',
                'fixed_discount',
                ['amount' => 1000],
            )
        );
        $this->setReferenceAndPersist(
            'fixed_discount_3',
            $this->createPromotionAction(
                'third_fixed_discount_for_first_orders',
                'fixed_discount',
                ['amount' => 511000],
            )
        );
        $this->setReferenceAndPersist(
            'fixed_discount_4',
            $this->createPromotionAction(
                'fourth_fixed_discount_coupon_only',
                'fixed_discount',
                ['amount' => 1000],
            )
        );
        $this->setReferenceAndPersist(
            'fixed_discount_5',
            $this->createPromotionAction(
                'fourth_fixed_discount_coupon_only_2',
                'fixed_discount',
                ['amount' => 6000000],
            )
        );

        $this->manager->flush();
    }

    private function createPromotionAction(
        string $promotion,
        string $type,
        array $configuration,
    ): PromotionAction {
        return (new PromotionAction())
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
