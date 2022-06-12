<?php

namespace App\DataFixtures;

use App\Entity\Seller;

class SellerFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "seller_lendo",
            $this->createSeller(
                'LENDO',
                'timcheh@lendo.ir',
                '123456',
                123456,
                '09111111111'
            )
        );

        $this->createMany(
            Seller::class,
            10,
            function (Seller $seller, int $i) {
                $this->createSeller(
                    $this->faker->unique()->company(),
                    $this->faker->unique()->username(),
                    '123456',
                    $this->faker->unique()->randomNumber(2),
                    '0911' . $this->faker->randomNumber(7),
                    $seller
                );
            },
            true
        );

        $this->manager->flush();
    }

    private function createSeller(
        string $name,
        string $username,
        string $password,
        int $identifier,
        string $mobile,
        ?Seller $seller = null,
        bool $isActive = true
    ): Seller {
        if (!$seller) {
            $seller = new Seller();
        }

        $seller->setName($name)
               ->setUsername($username)
               ->setPassword($this->faker->encodePassword($seller, $password))
               ->setIdentifier($identifier)
               ->setMobile($mobile)
               ->setIsActive($isActive);

        return $seller;
    }
}
