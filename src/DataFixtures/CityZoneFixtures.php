<?php

namespace App\DataFixtures;

use App\Entity\CityZone;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CityZoneFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "city_zone_express",
            $this->createCityZone(
                ['city_tehran'],
                'express_zone'
            )
        );

        $this->setReferenceAndPersist(
            "city_zone_non_express",
            $this->createCityZone(
                ['city_1', 'city_2'],
                'non_express_zone'
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
            CityFixtures::class,
        ];
    }

    private function createCityZone(array $cities, string $name): CityZone
    {
        $cityZone = (new CityZone())->setName($name);

        foreach ($cities as $city) {
            $cityZone->addCity($this->getReference($city));
        }

        return $cityZone;
    }
}
