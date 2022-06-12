<?php

namespace App\DataFixtures;

use App\Entity\Configuration;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ConfigurationFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "cod_default_gateway",
            $this->createConfiguration(
                'DEFAULT_COD_GATEWAY',
                'Zibal-COD'
            )
        );
        $product_5_id  = $this->getReference('product_5')->getId();
        $product_7_id  = $this->getReference('product_7')->getId();
        $product_13_id = $this->getReference('product_13')->getId();

        $this->setReferenceAndPersist(
            "on_sale_product",
            $this->createConfiguration(
                'ON_SALE_PRODUCTS',
                [
                    ['id' => $product_5_id, 'priority' => 0],
                    ['id' => $product_7_id, 'priority' => 1],
                    ['id' => $product_13_id, 'priority' => 2],
                ]
            )
        );

        $inventory_24_id = $this->getReference('inventory_24')->getId();

        $this->setReferenceAndPersist(
            "on_sale_inventory",
            $this->createConfiguration(
                'ON_SALE_INVENTORY',
                [
                    ['id' => $inventory_24_id, 'priority' => 0],
                ]
            )
        );
        $this->setReferenceAndPersist(
            "minimum_cart",
            $this->createConfiguration(
                'MINIMUM_CART',
                '100000'
            )
        );

        $this->manager->flush();
    }

    private function createConfiguration(string $code, string|array $value): Configuration
    {
        return (new Configuration())->setCode($code)->setValue($value);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            InventoryFixtures::class
        ];
    }
}
