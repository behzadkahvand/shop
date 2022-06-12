<?php

namespace App\DataFixtures;

use App\Entity\CartItem;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CartItemFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("cart_item_1", $this->createCartItem(
            'cart_tehran',
            100000,
            90000,
            100000,
            90000,
            1,
            'inventory_1'
        ));

        $this->setReferenceAndPersist("cart_item_2", $this->createCartItem(
            'cart_tehran',
            105000,
            105000,
            105000,
            105000,
            1,
            'inventory_2'
        ));

        $this->setReferenceAndPersist("cart_item_3", $this->createCartItem(
            'cart_tehran',
            200000,
            200000,
            200000,
            200000,
            1,
            'inventory_3'
        ));

        $this->setReferenceAndPersist("cart_item_4", $this->createCartItem(
            'cart_tehran',
            250000,
            249000,
            250000,
            249000,
            1,
            'inventory_4'
        ));

        $this->setReferenceAndPersist("cart_item_5", $this->createCartItem(
            'cart_tehran',
            250000,
            245000,
            250000,
            245000,
            1,
            'inventory_5'
        ));

        $this->setReferenceAndPersist("cart_item_6", $this->createCartItem(
            'cart_other_cities',
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->numberBetween(1, 3),
            'inventory_1'
        ));

        $this->setReferenceAndPersist("cart_item_7", $this->createCartItem(
            'cart_other_cities',
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->numberBetween(1, 3),
            'inventory_7'
        ));

        $this->setReferenceAndPersist("cart_item_8", $this->createCartItem(
            'cart_other_cities',
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->numberBetween(1, 3),
            'inventory_8'
        ));

        $this->setReferenceAndPersist("cart_item_9", $this->createCartItem(
            'cart_other_cities',
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->numberBetween(1, 3),
            'inventory_4'
        ));

        $this->setReferenceAndPersist("cart_item_10", $this->createCartItem(
            'cart_other_cities',
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->randomNumber(),
            $this->faker->numberBetween(1, 3),
            'inventory_10'
        ));

        $this->setReferenceAndPersist("cart_item_11", $this->createCartItem(
            'cart_tehran_customer_4',
            511000,
            511000,
            511000,
            511000,
            1,
            'inventory_10'
        ));

        $this->setReferenceAndPersist("cart_item_12", $this->createCartItem(
            'cart_tehran_customer_5',
            420000,
            415000,
            420000,
            415000,
            1,
            'inventory_17'
        ));

        $this->setReferenceAndPersist("cart_item_13", $this->createCartItem(
            'cart_tehran_customer_5',
            190000,
            188000,
            190000,
            188000,
            1,
            'inventory_18'
        ));

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CartFixtures::class,
            InventoryFixtures::class,
        ];
    }

    private function createCartItem(
        string $cart,
        int $subTotal,
        int $grandTotal,
        int $price,
        int $finalPrice,
        int $quantity,
        string $inventory
    ): CartItem {
        return (new CartItem())
            ->setCart($this->getReference($cart))
            ->setSubtotal($subTotal)
            ->setGrandTotal($grandTotal)
            ->setPrice($price)
            ->setFinalPrice($finalPrice)
            ->setQuantity($quantity)
            ->setInventory($this->getReference($inventory));
    }
}
