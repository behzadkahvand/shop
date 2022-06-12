<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\AbstractProductSearchDataEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;

final class SellerSearchDataValidationEventListener extends AbstractSearchDataValidationEventListener
{
    /**
     * @inheritDoc
     */
    public function onProductSearchData(AbstractProductSearchDataEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_SELLER)
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
            'brand.id',
            'category.code',
            'title',
            'isOriginal',
            'status',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getValidSorts(): array
    {
        return [];
    }
}
