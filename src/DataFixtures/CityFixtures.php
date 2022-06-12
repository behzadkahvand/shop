<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CityFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->createMany(
            City::class,
            2,
            function (City $city, int $i) {
                $this->createCity(
                    $city,
                    'province_' . $i,
                    $this->faker->sentence(2)
                );
            },
            true
        );

        $this->setReferenceAndPersist(
            "city_tehran",
            $this->createCity(
                new City(),
                'province_tehran',
                'تهران'
            )
        );

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            ProvinceFixtures::class,
        ];
    }

    private function createCity(City $city, string $province, string $name): City
    {
        return $city->setName($name)->setProvince($this->getReference($province));
    }
}
