<?php

namespace App\DataFixtures;

use App\Entity\Holiday;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class HolidayFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $holiday = (new Holiday())
            ->setSeller($this->getReference('seller_lendo'))
            ->setTitle('seller holiday')
            ->setDate($this->faker->dateTimeBetween('-30 days', 'now'))
            ->setSupply(true);

        $this->addReference('holiday_1', $holiday);

        $this->manager->persist($holiday);
        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            SellerFixtures::class,
        ];
    }
}
