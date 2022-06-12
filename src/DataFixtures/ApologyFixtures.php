<?php

namespace App\DataFixtures;

use App\Entity\Apology;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ApologyFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $apology = (new Apology())
            ->setPromotion($this->getReference("fourth_fixed_discount_coupon_only"))
            ->setCodePrefix("apology_1_")
            ->setMessageTemplate("Dear %first_name%, Apology");

        $this->addReference('apology_one', $apology);

        $this->manager->persist($apology);
        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            PromotionFixtures::class,
        ];
    }
}
