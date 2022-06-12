<?php

namespace App\DataFixtures;

use App\Entity\ReturnRequest;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ReturnRequestFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->createMany(ReturnRequest::class, 3, function (ReturnRequest $returnRequest, int $count) {
            $returnRequest
                ->setOrder($this->getReference('order_13'))
                ->setReturnDate($this->faker->dateTimeBetween('now', '+10 days'))
                ->setCreatedBy('nimda@timcheh.ir');
        }, true);

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
        ];
    }
}
