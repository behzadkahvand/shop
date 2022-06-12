<?php

namespace App\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\Product\Search\Listeners\AbstractFilterAndSortMappingEventListener;
use App\Service\Product\Search\Utils\SearchDataMapping\Adapters\StaticListSellerSearchDataMappingAdapter;
use App\Service\Utils\WebsiteAreaService;

final class FilterAndSortMappingEventListener extends AbstractFilterAndSortMappingEventListener
{
    public function __construct(
        WebsiteAreaService $areaService,
        StaticListSellerSearchDataMappingAdapter $mapping
    ) {
        parent::__construct($areaService, $mapping);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [SellerProductSearchDataEvent::class => ['onProductSearchData', 110]];
    }
}
