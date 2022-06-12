<?php

namespace App\DataFixtures;

use App\Entity\Delivery;

class CategoryDeliveryFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("delivery_normal", $this->createCategoryDelivery(3, 5));
        $this->setReferenceAndPersist("delivery_heavy", $this->createCategoryDelivery(3, 5));
        $this->setReferenceAndPersist("delivery_super_heavy", $this->createCategoryDelivery(2, 4));
        $this->setReferenceAndPersist("delivery_fmcg", $this->createCategoryDelivery(3, 5));

        $this->manager->flush();
    }

    private function createCategoryDelivery(int $start, int $end): Delivery
    {
        return (new Delivery())->setStart($start)->setEnd($end);
    }
}
