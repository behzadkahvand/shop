<?php

namespace App\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\ProductStatusDictionary;
use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DefaultFiltersEventListener
 */
final class CustomerDefaultFiltersEventListener implements EventSubscriberInterface
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
        return [SellerProductSearchDataEvent::class => ['onProductSearchDataEvent', 2]];
    }

    /**
     * @param SellerProductSearchDataEvent $event
     *
     * @return void
     */
    public function onProductSearchDataEvent(SellerProductSearchDataEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_CUSTOMER)
        ) {
            return;
        }

        $filters = $event->getData()->getFilters();

        $title = null;

        if (!isset($filters['title'])) {
            $title = null;
        } elseif (is_array($filters['title'])) {
            $title = current($filters['title']);
        } else {
            $title = $filters['title'];
        }

        unset($filters['title']);

        if (!isset($filters['status'])) {
            $filters['status']['in'] = implode(',', [
                ProductStatusDictionary::SOON,
                ProductStatusDictionary::CONFIRMED,
                ProductStatusDictionary::UNAVAILABLE,
                ProductStatusDictionary::SHUTDOWN,
            ]);
        }

        if (!isset($filters['productVariants.inventories.finalPrice'])) {
            $filters['productVariants.inventories.id']['gt'] = 0;
        }

        $event->setData(new DoctrineSearchData($filters, $event->getData()->getSorts(), null, $title));
    }
}
