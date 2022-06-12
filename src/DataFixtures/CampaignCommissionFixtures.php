<?php

namespace App\DataFixtures;

use App\Entity\CampaignCommission;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CampaignCommissionFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist("campaign_commission_1", $this->createCampaignCommission(
            'category_tv',
            'brand_2',
            'seller_3',
            $this->faker->dateTimeBetween("+1 days", "+1 days"),
            $this->faker->dateTimeBetween("+2 days", "+2 days"),
            1.2
        ));

        $this->setReferenceAndPersist("campaign_commission_2", $this->createCampaignCommission(
            'category_tv',
            'brand_3',
            'seller_4',
            $this->faker->dateTimeBetween("+3 days", "+3 days"),
            $this->faker->dateTimeBetween("+4 days", "+4 days"),
            1.9
        ));

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            BrandFixtures::class,
            SellerFixtures::class,
        ];
    }

    private function createCampaignCommission(
        string $category,
        string $brand,
        string $seller,
        DateTime $startDate,
        DateTime $endDate,
        float $fee
    ): CampaignCommission {
        return (new CampaignCommission())
            ->setCategory($this->getReference($category))
            ->setBrand($this->getReference($brand))
            ->setSeller($this->getReference($seller))
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setFee($fee);
    }
}
