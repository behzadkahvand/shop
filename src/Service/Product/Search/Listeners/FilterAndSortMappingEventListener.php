<?php

namespace App\Service\Product\Search\Listeners;

use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\Utils\SearchDataMapping\Adapters\StaticListSearchDataMappingAdapter;
use App\Service\Utils\WebsiteAreaService;

final class FilterAndSortMappingEventListener extends AbstractFilterAndSortMappingEventListener
{
    public function __construct(
        WebsiteAreaService $areaService,
        StaticListSearchDataMappingAdapter $mapping
    ) {
        parent::__construct($areaService, $mapping);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [ProductSearchDataEvent::class => ['onProductSearchData', 110]];
    }
}
