<?php

namespace App\DataFixtures;

use App\Entity\Wishlist;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class WishlistFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'wishlist_1',
            $this->createWishlist(
                'customer_1',
                'product_10'
            )
        );

        $this->manager->flush();
    }

    private function createWishlist(string $customer, string $product): Wishlist
    {
        return (new Wishlist())->setCustomer($this->getReference($customer))
                               ->setProduct($this->getReference($product));
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            CustomerFixtures::class,
            ProductFixtures::class
        ];
    }
}
