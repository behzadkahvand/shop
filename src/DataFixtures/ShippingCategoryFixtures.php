<?php

namespace App\DataFixtures;

use App\Entity\ShippingCategory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ShippingCategoryFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'shipping_category_1',
            $this->createShippingCategory(
                'NORMAL',
                ['shipping_method_1', 'shipping_method_3'],
                'delivery_normal'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_category_2',
            $this->createShippingCategory(
                'HEAVY',
                ['shipping_method_1', 'shipping_method_4'],
                'delivery_heavy'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_category_3',
            $this->createShippingCategory(
                'SUPER_HEAVY',
                ['shipping_method_2', 'shipping_method_5'],
                'delivery_super_heavy'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_category_4',
            $this->createShippingCategory(
                'FMCG',
                ['shipping_method_1', 'shipping_method_6'],
                'delivery_fmcg'
            )
        );

        $this->manager->flush();
    }

    private function createShippingCategory(
        string $name,
        array $methods,
        string $categoryDelivery
    ): ShippingCategory {
        $shippingCategory = (new ShippingCategory())->setName($name)
                                                    ->setDelivery($this->getReference($categoryDelivery));

        foreach ($methods as $method) {
            $shippingCategory->addMethod($this->getReference($method));
        }

        return $shippingCategory;
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            ShippingMethodFixtures::class,
            CategoryDeliveryFixtures::class
        ];
    }
}
