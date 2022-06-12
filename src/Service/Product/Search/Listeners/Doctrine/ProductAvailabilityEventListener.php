<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ProductStatusDictionary;
use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductAvailabilityEventListener
 */
final class ProductAvailabilityEventListener implements EventSubscriberInterface
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
        return [ProductSearchDataEvent::class => ['onProductSearchQueryEvent', 3]];
    }

    /**
     * @param ProductSearchDataEvent $event
     */
    public function onProductSearchQueryEvent(ProductSearchDataEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_CUSTOMER)
        ) {
            return;
        }

        $filters = $event->getData()->getFilters();

        if (isset($filters['buyBox.finalPrice'])) {
            $filters['isAvailable'] = true;
        }

        if (!isset($filters['isAvailable'])) {
            return;
        }

        if (
            isset($filters['isAvailable'])
            && !in_array($filters['isAvailable'], [true, 'true', 1, '1'], true)
        ) {
            throw new SearchDataValidationException('Product availability filter is invalid!');
        }

        $filters['status']['in'] = implode(',', [ProductStatusDictionary::CONFIRMED, ProductStatusDictionary::SOON]);

        unset($filters['isAvailable']);

        $event->setData(new SearchData($filters, $event->getData()->getSorts()));
    }
}
