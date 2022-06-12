<?php

namespace App\DataFixtures;

use App\Entity\ProductNotifyRequest;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductNotifyRequestFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist($this->createProductNotifyRequest('customer_1', 'product_1'));

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
            CustomerFixtures::class,
        ];
    }

    private function createProductNotifyRequest(string $customer, string $product): ProductNotifyRequest
    {
        return (new ProductNotifyRequest())
            ->setProduct($this->getReference($product))
            ->setCustomer($this->getReference($customer));
    }
}
