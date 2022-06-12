<?php

namespace App\DataFixtures;

use App\Entity\ShippingPeriod;
use DateTimeInterface;

class ShippingPeriodFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'shipping_period_1',
            $this->createShippingPeriod(
                $this->faker->specificTime(9),
                $this->faker->specificTime(13)
            )
        );

        $this->setReferenceAndPersist(
            'shipping_period_2',
            $this->createShippingPeriod(
                $this->faker->specificTime(14),
                $this->faker->specificTime(18)
            )
        );

        $this->setReferenceAndPersist(
            'shipping_period_3',
            $this->createShippingPeriod(
                $this->faker->specificTime(18),
                $this->faker->specificTime(22)
            )
        );

        $this->manager->flush();
    }

    private function createShippingPeriod(
        DateTimeInterface $start,
        DateTimeInterface $end,
        bool $isActive = true
    ): ShippingPeriod {
        return (new ShippingPeriod())->setStart($start)->setEnd($end)->setIsActive($isActive);
    }
}
