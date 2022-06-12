<?php

namespace App\Service\Product\Search\Listeners\Doctrine\Seller;

use App\Dictionary\WebsiteAreaDictionary;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerDefaultSortsEventListener
 */
final class CustomerDefaultSortsEventListener implements EventSubscriberInterface
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
        return [SellerProductSearchDataEvent::class => ['onProductSearchDataEvent', 1]];
    }

    public function onProductSearchDataEvent(SellerProductSearchDataEvent $event): void
    {
        if (
            DoctrineProductSearchDriver::class !== $event->getDriverFQN() ||
            !$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_CUSTOMER)
        ) {
            return;
        }

        $sorts = $event->getData()->getSorts();

        if (!empty($sorts)) {
            return;
        }

        $sorts[] = '-visits';

        $event->setData(new DoctrineSearchData($event->getData()->getFilters(), $sorts, $event->getData()->getCategoryCode(), $event->getData()->getTitle()));
    }
}
