<?php

namespace App\DataFixtures;

use App\Entity\RateAndReview;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class RateAndReviewFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createRateAndReview(
                'product_1',
                'customer_1',
                'inventory_1',
                'order_1',
                'Title of review',
                'Body of review',
                'YES',
                $this->faker->numberBetween(1, 5),
                false,
                'ACCEPTED'
            )
        );
        $this->manager->persist(
            $this->createRateAndReview(
                'product_2',
                'customer_1',
                'inventory_1',
                'order_1',
                'Title of review',
                'Body of review',
                'YES',
                $this->faker->numberBetween(1, 5),
                false,
                'ACCEPTED'
            )
        );

        $this->manager->flush();
    }

    private function createRateAndReview(
        string $product,
        string $customer,
        string $inventory,
        string $order,
        string $title,
        string $body,
        string $suggestion,
        int $rate,
        bool $anonymous,
        string $status,
    ): RateAndReview {
        return (new RateAndReview())
            ->setProduct($this->getReference($product))
            ->setCustomer($this->getReference($customer))
            ->setInventory($this->getReference($inventory))
            ->setOrder($this->getReference($order))
            ->setTitle($title)
            ->setBody($body)
            ->setSuggestion($suggestion)
            ->setRate($rate)
            ->setAnonymous($anonymous)
            ->setStatus($status);
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
            CustomerFixtures::class,
            InventoryFixtures::class,
            OrderFixtures::class,
        ];
    }
}
