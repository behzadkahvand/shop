<?php

namespace App\DataFixtures;

use App\Entity\OrderNote;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderNoteFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->createMany(
            OrderNote::class,
            50,
            function (OrderNote $orderNote, $count) {
                $orderNote->setOrder($this->getReference('order_' . $this->faker->numberBetween(1, 5)))
                ->setAdmin($this->getReference('admin_1'))
                ->setDescription($this->faker->sentence(20));
            }
        );

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            AdminFixtures::class,
            OrderFixtures::class,
        ];
    }
}
