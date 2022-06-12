<?php

namespace App\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\AbstractProductSearchDataEvent;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\AbstractSearchDataValidationEventListener;

/**
 * Class SearchDataValidationEventListener
 */
final class CustomerSearchDataValidationEventListener extends AbstractSearchDataValidationEventListener
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [SellerProductSearchDataEvent::class => ['onProductSearchData', 100]];
    }

    /**
     * @inheritDoc
     */
    public function onProductSearchData(AbstractProductSearchDataEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_CUSTOMER)
        ) {
            return;
        }

        parent::onProductSearchData($event);
    }

    /**
     * @inheritDoc
     */
    protected function getValidFilters(): array
    {
        return [
            'productVariants.inventories.finalPrice',
            'productVariants.inventories.seller.identifier',
            'brand.id',
            'brand.code',
            'category.code',
            'title',
            'isAvailable',
            'isOriginal',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getValidSorts(): array
    {
        return [
            'productVariants.inventories.finalPrice',
            'visits',
            'orderCount',
            'productVariants.inventories.createdAt',
            'productVariants.inventories.leadTime',
        ];
    }
}
