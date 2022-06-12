<?php

namespace App\Service\Product\Search\Listeners\Doctrine;

use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\ProductSearchResultEvent;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SellerInventoryHasStockFilter
 */
final class SellerInventoryHasStockFilterListener implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    private WebsiteAreaService $websiteAreaService;

    /**
     * CustomerHasStockFilterListener constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param WebsiteAreaService $websiteAreaService
     */
    public function __construct(EntityManagerInterface $entityManager, WebsiteAreaService $websiteAreaService)
    {
        $this->entityManager = $entityManager;
        $this->websiteAreaService = $websiteAreaService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductSearchDataEvent::class   => ['onProductSearchDataEvent', 1000],
            ProductSearchResultEvent::class => 'onProductSearchResultEvent',
        ];
    }

    /**
     * @param ProductSearchDataEvent $event
     */
    public function onProductSearchDataEvent(ProductSearchDataEvent $event): void
    {
        if ($this->shouldSkip($event)) {
            return;
        }

        $this->entityManager->getFilters()->enable('inventoryHasStock');
    }

    /**
     * @param ProductSearchResultEvent $event
     */
    public function onProductSearchResultEvent(ProductSearchResultEvent $event): void
    {
        if ($this->shouldSkip($event)) {
            return;
        }

        $this->entityManager->getFilters()->disable('inventoryHasStock');
    }

    /**
     * @param $event
     *
     * @return bool
     */
    private function shouldSkip($event): bool
    {
        return DoctrineProductSearchDriver::class !== $event->getDriverFQN()
            || !$this->websiteAreaService->isSellerArea();
    }
}
