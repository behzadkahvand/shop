<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SellerSearchByProductIdFilterListener
 */
final class SellerSearchByProductIdFilterListener implements EventSubscriberInterface
{
    /**
     * @var WebsiteAreaService
     */
    private WebsiteAreaService $areaService;

    /**
     * SellerSearchByProductIdFilterListener constructor.
     *
     * @param WebsiteAreaService $areaService
     */
    public function __construct(WebsiteAreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductSearchDataEvent::class => ['onProductSearchDataEvent', 1],
        ];
    }

    /**
     * @param ProductSearchDataEvent $event
     */
    public function onProductSearchDataEvent(ProductSearchDataEvent $event): void
    {
        if (DoctrineProductSearchDriver::class !== $event->getDriverFQN() || !$this->areaService->isSellerArea()) {
            return;
        }

        $title = $event->getData()->getTitle();

        if (null === $title) {
            return;
        }

        if (!preg_match('#^(tpi-)?\d+$#i', $title)) {
            return;
        }

        $filters = array_merge($event->getData()->getFilters(), [
            'id' => (int) preg_replace('#\D+#', '', $title),
        ]);

        $event->setData(new DoctrineSearchData(
            $filters,
            $event->getData()->getSorts(),
            $event->getData()->getCategoryCode(),
            null
        ));
    }
}
