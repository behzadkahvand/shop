<?php

namespace App\DataFixtures;

use App\Entity\OrderAddress;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;

class OrderAddressFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->createMany(
            OrderAddress::class,
            13,
            function (OrderAddress $orderAddress, int $i) {
                $customerAddress = $this->findCustomerAddress($i);

                $this->createOrderAddress(
                    $orderAddress,
                    'order_' . $i,
                    $this->faker->sentence(4),
                    $this->faker->randomNumber(),
                    $this->faker->point(),
                    $this->faker->randomNumber(),
                    $this->faker->randomNumber(),
                    $this->faker->randomNumber(2),
                    $this->faker->sentence(2),
                    $this->faker->sentence(2),
                    $this->faker->randomNumber(),
                    $this->faker->randomNumber(),
                    'city_' . $this->faker->numberBetween(1, 2),
                    'district_' . $this->faker->numberBetween(1, 2),
                    $customerAddress
                );
            },
            true
        );

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
            CityFixtures::class,
            DistrictFixtures::class,
            CustomerAddressFixtures::class,
        ];
    }

    protected function createOrderAddress(
        OrderAddress $orderAddress,
        string $order,
        string $fullAddress,
        string $postalCode,
        AbstractPoint $coordinates,
        int $number,
        int $unit,
        int $floor,
        string $name,
        string $family,
        string $nationalCode,
        string $phone,
        string $city,
        string $district,
        string $customerAddress,
        bool $isActive = true
    ): void {
        $orderAddress->setFullAddress($fullAddress)
                     ->setFullAddress($fullAddress)
                     ->setPostalCode($postalCode)
                     ->setCoordinates($coordinates)
                     ->setNumber($number)
                     ->setUnit($unit)
                     ->setFloor($floor)
                     ->setName($name)
                     ->setFamily($family)
                     ->setNationalCode($nationalCode)
                     ->setPhone($phone)
                     ->setIsActive($isActive)
                     ->setOrder($this->getReference($order))
                     ->setCity($this->getReference($city))
                     ->setDistrict($this->getReference($district))
                     ->setCustomerAddress($this->getReference($customerAddress));
    }

    protected function findCustomerAddress(int $i): string
    {
        $customerAddress = "customer_address_1";

        if ($i == 3) {
            $customerAddress = "customer_address_2";
        } elseif (in_array($i, [4, 8, 9])) {
            $customerAddress = "customer_address_3";
        } elseif ($i == 10) {
            $customerAddress = "customer_address_8";
        } elseif (in_array($i, [11, 12, 13])) {
            $customerAddress = "customer_address_11";
        }

        return $customerAddress;
    }
}
