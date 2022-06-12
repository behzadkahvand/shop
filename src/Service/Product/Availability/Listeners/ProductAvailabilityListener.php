<?php

namespace App\Service\Product\Availability\Listeners;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Inventory;
use App\Entity\Product;
use App\Messaging\Messages\Command\Product\NotifyAvailableProduct;
use App\Service\Product\Availability\ProductAvailabilityChecker;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ProductAvailabilityListener
{
    public function __construct(
        private ProductAvailabilityChecker $availabilityChecker,
        private MessageBusInterface $bus
    ) {
    }

    public function onInventoryPreFlush(Inventory $inventory, EventArgs $event): void
    {
        if (!$this->availabilityChecker->inventoryIsEligibleToChangeProductAvailability($inventory)) {
            return;
        }

        /** @var Product $product */
        $product = $inventory->getVariant()->getProduct();
        $em      = $event->getEntityManager();

        if ($this->productIsAvailable($product)) {
            if ($this->productShouldBeUnavailable($product)) {
                $this->changeProductStatus($product, $em, ProductStatusDictionary::UNAVAILABLE);
            }

            return;
        }

        if ($this->productShouldBeAvailable($product)) {
            $this->changeProductStatus($product, $em, ProductStatusDictionary::CONFIRMED);
            $this->bus
                ->dispatch(
                    async_message(new NotifyAvailableProduct($product->getId()))
                );
        }
    }

    private function productIsAvailable(Product $product): bool
    {
        return $this->availabilityChecker->isAvailable($product);
    }

    private function productShouldBeUnavailable(Product $product): bool
    {
        return $this->availabilityChecker->shouldBeUnavailable($product);
    }

    private function productShouldBeAvailable(Product $product): bool
    {
        return $this->availabilityChecker->shouldBeAvailable($product);
    }

    private function changeProductStatus(Product $product, EntityManagerInterface $em, string $status): void
    {
        $product->setStatus($status);

        $meta = $em->getClassMetadata(get_class($product));

        $method = null !== $product->getId() ? 'recomputeSingleEntityChangeSet' : 'computeChangeSet';

        $em->getUnitOfWork()->$method($meta, $product);
    }
}
