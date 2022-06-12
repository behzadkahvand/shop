<?php

namespace App\DataFixtures;

use App\Entity\CustomerAddress;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class CustomerAddressFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "customer_address_tehran",
            $this->createCustomerAddress(
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_tehran',
                'city_tehran',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_1'
            )
        );
        $this->setReferenceAndPersist(
            "customer_address_1",
            $this->createCustomerAddress(
                $this->faker->sentence(4),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_1',
                'city_tehran',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_1'
            )
        );
        $this->setReferenceAndPersist(
            "customer_address_2",
            $this->createCustomerAddress(
                $this->faker->sentence(4),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_1',
                'city_1',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_1'
            )
        );
        $this->setReferenceAndPersist(
            "customer_address_3",
            $this->createCustomerAddress(
                $this->faker->sentence(4),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_1',
                'city_1',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_3'
            )
        );
        $this->setReferenceAndPersist(
            "customer_address_4",
            $this->createCustomerAddress(
                $this->faker->sentence(4),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_1',
                'city_1',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_1'
            )
        );
        $this->setReferenceAndPersist(
            "customer_address_5",
            $this->createCustomerAddress(
                $this->faker->sentence(4),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_1',
                'city_tehran',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_4'
            )
        );
        $this->setReferenceAndPersist(
            "customer_address_6",
            $this->createCustomerAddress(
                $this->faker->sentence(4),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_1',
                'city_1',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_4'
            )
        );
        $this->setReferenceAndPersist(
            "customer_address_7",
            $this->createCustomerAddress(
                $this->faker->sentence(4),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_1',
                'city_1',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_5'
            )
        );
        $this->setReferenceAndPersist(
            "customer_address_8",
            $this->createCustomerAddress(
                $this->faker->sentence(4),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_1',
                'city_1',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_5'
            )
        );
        $this->setReferenceAndPersist(
            "customer_address_11",
            $this->createCustomerAddress(
                $this->faker->sentence(4),
                $this->faker->randomNumber(),
                $this->faker->boolean(),
                $this->faker->point(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(2),
                $this->faker->sentence(2),
                $this->faker->sentence(2),
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                'province_1',
                'city_1',
                'district_' . $this->faker->numberBetween(1, 2),
                'customer_5'
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
            CustomerFixtures::class,
            CityFixtures::class,
            ProvinceFixtures::class,
            DistrictFixtures::class,
        ];
    }

    private function createCustomerAddress(
        string $fullAddress,
        string $postalCode,
        bool $isDefault,
        AbstractPoint $coordinates,
        int $number,
        int $unit,
        int $floor,
        string $name,
        string $family,
        string $nationalCode,
        string $mobile,
        string $province,
        string $city,
        string $district,
        string $customer
    ): CustomerAddress {
        return (new CustomerAddress())
            ->setFullAddress($fullAddress)
            ->setPostalCode($postalCode)
            ->setIsDefault($isDefault)
            ->setCoordinates($coordinates)
            ->setNumber($number)
            ->setUnit($unit)
            ->setFloor($floor)
            ->setName($name)
            ->setFamily($family)
            ->setNationalCode($nationalCode)
            ->setMobile($mobile)
            ->setProvince($this->getReference($province))
            ->setCity($this->getReference($city))
            ->setDistrict($this->getReference($district))
            ->setCustomer($this->getReference($customer));
    }
}
