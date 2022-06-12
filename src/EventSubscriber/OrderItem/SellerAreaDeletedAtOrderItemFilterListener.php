<?php

namespace App\EventSubscriber\OrderItem;

use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SellerAreaDeletedAtOrderItemFilterListener
 */
final class SellerAreaDeletedAtOrderItemFilterListener implements EventSubscriberInterface
{
    protected EntityManagerInterface $manager;

    protected WebsiteAreaService $websiteAreaService;

    public function __construct(EntityManagerInterface $manager, WebsiteAreaService $websiteAreaService)
    {
        $this->manager            = $manager;
        $this->websiteAreaService = $websiteAreaService;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 1]];
    }

    public function onKernelRequest(KernelEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->websiteAreaService->isSellerArea()) {
            return;
        }

        $this->manager->getFilters()->disable('softdeleteable');
    }
}
