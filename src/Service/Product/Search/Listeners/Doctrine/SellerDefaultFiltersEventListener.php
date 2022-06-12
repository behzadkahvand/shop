<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ProductStatusDictionary;
use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SellerDefaultFiltersEventListener implements EventSubscriberInterface
{
    protected WebsiteAreaService $websiteAreaService;

    public function __construct(WebsiteAreaService $websiteAreaService)
    {
        $this->websiteAreaService = $websiteAreaService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [ProductSearchDataEvent::class => ['onProductSearchDataEvent', 2]];
    }

    /**
     * @param ProductSearchDataEvent $event
     *
     * @return void
     */
    public function onProductSearchDataEvent(ProductSearchDataEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_SELLER)
        ) {
            return;
        }

        $filters = $event->getData()->getFilters();

        $validStatuses = [
            ProductStatusDictionary::SOON,
            ProductStatusDictionary::CONFIRMED,
            ProductStatusDictionary::UNAVAILABLE,
        ];

        if (isset($filters['status']) && !in_array($filters['status'], $validStatuses, true)) {
            throw new SearchDataValidationException('Product status filter is invalid!');
        }

        if (!isset($filters['status'])) {
            $filters['status']['in'] = implode(',', $validStatuses);
        }

        if (!isset($filters['title'])) {
            $title = null;
        } elseif (is_array($filters['title'])) {
            $title = current($filters['title']);
        } else {
            $title = $filters['title'];
        }

        unset($filters['title']);

        $event->setData(new DoctrineSearchData($filters, $event->getData()->getSorts(), null, $title));
    }
}
