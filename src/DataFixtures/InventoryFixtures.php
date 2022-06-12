<?php

namespace App\DataFixtures;

use App\Entity\Inventory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class InventoryFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "inventory_1",
            $this->createInventory(
                $this->faker->numberBetween(6, 15),
                100000,
                90000,
                $this->faker->numberBetween(1, 10),
                1,
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_lendo',
                'product_variant_1'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_2",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                105000,
                105000,
                $this->faker->numberBetween(1, 10),
                0,
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_4',
                'product_variant_1'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_3",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                200000,
                200000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_4',
                'product_variant_2'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_4",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                250000,
                249000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_3',
                'product_variant_3'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_5",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                250000,
                245000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_lendo',
                'product_variant_3'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_6",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                145000,
                139500,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_4',
                'product_variant_4'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_7",
            $this->createInventory(
                0,
                160000,
                160000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_5',
                'product_variant_5'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_8",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                540000,
                540000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_6',
                'product_variant_6'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_9",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                510000,
                500000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_lendo',
                'product_variant_7'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_10",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                511000,
                511000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_5',
                'product_variant_7'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_11",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                520000,
                510000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_8',
                'product_variant_8'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_12",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                320000,
                320000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_9',
                'product_variant_9'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_13",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                352000,
                352000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_10',
                'product_variant_10'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_14",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                465000,
                449500,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_lendo',
                'product_variant_11'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_15",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                466000,
                460000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_6',
                'product_variant_11'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_16",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                425000,
                425000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_2',
                'product_variant_12'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_17",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                420000,
                415000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_lendo',
                'product_variant_12',
                true
            )
        );
        $this->setReferenceAndPersist(
            "inventory_18",
            $this->createInventory(
                $this->faker->numberBetween(1, 10),
                190000,
                188000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_lendo',
                'product_variant_13',
                null,
                false
            )
        );
        $this->setReferenceAndPersist(
            "inventory_19",
            $this->createInventory(
                0,
                0,
                0,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_lendo',
                'product_variant_2'
            )
        );
        $this->setReferenceAndPersist(
            "inventory_20",
            $this->createInventory(
                1,
                20,
                20,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_1',
                'product_variant_1',
            )
        );
        $this->setReferenceAndPersist(
            "inventory_21",
            $this->createInventory(
                1,
                5986000,
                20,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_1',
                'product_variant_12',
            )
        );
        $this->setReferenceAndPersist(
            "inventory_22",
            $this->createInventory(
                1,
                15000,
                20,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_1',
                'product_variant_13',
            )
        );
        $this->setReferenceAndPersist(
            "inventory_23",
            $this->createInventory(
                0,
                190000,
                188000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_lendo',
                'product_variant_14',
            )
        );
        $this->setReferenceAndPersist(
            "inventory_24",
            $this->createInventory(
                10,
                110000,
                100000,
                $this->faker->numberBetween(1, 10),
                $this->faker->numberBetween(2, 10),
                'CONFIRMED',
                $this->faker->randomNumber(),
                'seller_10',
                'product_variant_15',
                true
            )
        );

        $this->manager->flush();

        $this->getReference('product_1')->setBuyBox($this->getReference('inventory_20'));
        $this->getReference('product_2')->setBuyBox($this->getReference('inventory_3'));
        $this->getReference('product_3')->setBuyBox($this->getReference('inventory_6'));
        $this->getReference('product_5')->setBuyBox($this->getReference('inventory_9'));
        $this->getReference('product_7')->setBuyBox($this->getReference('inventory_12'));
        $this->getReference('product_8')->setBuyBox($this->getReference('inventory_17'));
        $this->getReference('product_13')->setBuyBox($this->getReference('inventory_24'));

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            SellerFixtures::class,
            ProductVariantFixtures::class,
        ];
    }

    private function createInventory(
        string $sellerStock,
        string $price,
        string $finalPrice,
        int $maxPurchasePerOrder,
        int $leadTime,
        string $status,
        int $orderCount,
        string $seller,
        string $productVariant,
        ?bool $hasCampaign = null,
        bool $isActive = true
    ): Inventory {
        $inventory = (new Inventory())
            ->setSellerStock($sellerStock)
            ->setPrice($price)
            ->setFinalPrice($finalPrice)
            ->setMaxPurchasePerOrder($maxPurchasePerOrder)
            ->setLeadTime($leadTime)
            ->setStatus($status)
            ->setOrderCount($orderCount)
            ->setSeller($this->getReference($seller))
            ->setVariant($this->getReference($productVariant))
            ->setIsActive($isActive);

        if ($hasCampaign !== null) {
            $inventory->setHasCampaign($hasCampaign);
        }

        return $inventory;
    }
}
