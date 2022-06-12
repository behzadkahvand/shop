<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CartFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("cart_tehran", $this->createCart(
            905000,
            889000,
            'customer_1'
        ));

        $this->setReferenceAndPersist("cart_tehran_customer_4", $this->createCart(
            511000,
            511000,
            'customer_4'
        ));

        $this->setReferenceAndPersist("cart_tehran_customer_5", $this->createCart(
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            'customer_5'
        ));

        $this->setReferenceAndPersist("cart_other_cities", $this->createCart(
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            'customer_2'
        ));

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CustomerFixtures::class
        ];
    }

    private function createCart(
        int $subTotal,
        int $grandTotal,
        string $customer
    ): Cart {
        return (new Cart())
            ->setSubtotal($subTotal)
            ->setGrandTotal($grandTotal)
            ->setCustomer($this->getReference($customer));
    }
}
