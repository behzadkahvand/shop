<?php

namespace App\DataFixtures;

use App\Entity\ReturnRequestItem;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ReturnRequestItemFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->createMany(
            ReturnRequestItem::class,
            4,
            function (ReturnRequestItem $returnRequestItem, int $count) {
                if ($count == 3 || $count == 4) {
                    $returnRequestItem->setRequest($this->getReference('return_request_' . ($count - 1)));
                } else {
                    $returnRequestItem->setRequest($this->getReference('return_request_' . $count));
                }
                $returnRequestItem
                ->setQuantity(2)
                ->setDescription('bla bla bla')
                ->setStatus('APPROVED')
                ->setIsReturnable(1)
                ->setRefundAmount(1000);
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
            ReturnRequestFixtures::class,
        ];
    }
}
