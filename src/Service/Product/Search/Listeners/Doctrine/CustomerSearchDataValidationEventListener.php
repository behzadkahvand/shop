<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\AbstractProductSearchDataEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;

/**
 * Class SearchDataValidationEventListener
 */
final class CustomerSearchDataValidationEventListener extends AbstractSearchDataValidationEventListener
{
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
            'buyBox.finalPrice',
            'brand.id',
            'brand.code',
            'category.code',
            'title',
            'isAvailable',
            'isOriginal',
            'hasPromotion',
            'buyBox.hasCampaign',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getValidSorts(): array
    {
        return [
            'buyBox.finalPrice',
            'visits',
            'orderCount',
            'buyBox.createdAt',
            'buyBox.leadTime',
            'promotion',
            'buyBox.hasCampaign',
        ];
    }
}
