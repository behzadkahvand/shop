<?php

namespace App\DataFixtures;

use App\Entity\District;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class DistrictFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("district_1", $this->createDistrict(
            'city_' . $this->faker->numberBetween(1, 2),
            $this->faker->sentence(2)
        ));
        $this->setReferenceAndPersist("district_2", $this->createDistrict(
            'city_' . $this->faker->numberBetween(1, 2),
            $this->faker->sentence(2)
        ));

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
        ];
    }

    private function createDistrict(string $city, string $name): District
    {
        return (new District())->setCity($this->getReference($city))->setName($name);
    }
}
