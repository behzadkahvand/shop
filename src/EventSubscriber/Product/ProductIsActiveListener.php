<?php

namespace App\EventSubscriber\Product;

use App\Dictionary\WebsiteAreaDictionary;
use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProductIsActiveListener implements EventSubscriberInterface
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

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->websiteAreaService->isArea(WebsiteAreaDictionary::AREA_CUSTOMER)) {
            return;
        }

        $this->manager->getFilters()->enable("productIsActive");
    }
}
