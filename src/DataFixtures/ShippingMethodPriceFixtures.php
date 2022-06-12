<?php

namespace App\DataFixtures;

use App\Entity\ShippingMethodPrice;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ShippingMethodPriceFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'shipping_method_price_express_1',
            $this->createShippingMethodPrice(
                0,
                'city_zone_express',
                'shipping_method_3'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_price_express_2',
            $this->createShippingMethodPrice(
                0,
                'city_zone_express',
                'shipping_method_4'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_price_express_3',
            $this->createShippingMethodPrice(
                0,
                'city_zone_express',
                'shipping_method_5'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_price_express_4',
            $this->createShippingMethodPrice(
                0,
                'city_zone_express',
                'shipping_method_6'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_price_non_express_1',
            $this->createShippingMethodPrice(
                0,
                'city_zone_non_express',
                'shipping_method_1'
            )
        );

        $this->setReferenceAndPersist(
            'shipping_method_price_non_express_2',
            $this->createShippingMethodPrice(
                0,
                'city_zone_non_express',
                'shipping_method_2'
            )
        );

        $this->manager->flush();
    }

    private function createShippingMethodPrice(
        int $price,
        string $cityZone,
        string $shippingMethod
    ): ShippingMethodPrice {
        return (new ShippingMethodPrice())->setPrice($price)
                                          ->setZone($this->getReference($cityZone))
                                          ->setShippingMethod($this->getReference($shippingMethod));
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
            ShippingMethodFixtures::class
        ];
    }
}
