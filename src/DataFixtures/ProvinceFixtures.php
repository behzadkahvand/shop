<?php

namespace App\DataFixtures;

use App\Entity\Province;

class ProvinceFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'province_1',
            $this->createProvince(
                'province_1',
                $this->faker->sentence(2)
            )
        );
        $this->setReferenceAndPersist(
            'province_2',
            $this->createProvince(
                'province_2',
                $this->faker->sentence(2)
            )
        );
        $this->setReferenceAndPersist(
            'province_tehran',
            $this->createProvince(
                'tehran',
                'tehran'
            )
        );

        $this->manager->flush();
    }

    private function createProvince(string $code, string $name,): Province
    {
        return (new Province())->setCode($code)->setName($name);
    }
}
