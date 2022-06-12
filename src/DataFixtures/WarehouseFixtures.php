<?php

namespace App\DataFixtures;

use App\Entity\Warehouse;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class WarehouseFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'warehouse_lendo',
            $this->createWarehouse(
                'LENDO',
                'seller_lendo'
            )
        );

        $this->manager->flush();
    }

    private function createWarehouse(string $name, string $seller): Warehouse
    {
        return (new Warehouse())->setName($name)->setSeller($this->getReference($seller));
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            SellerFixtures::class
        ];
    }
}
