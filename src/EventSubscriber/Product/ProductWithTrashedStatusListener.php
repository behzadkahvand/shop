<?php

namespace App\EventSubscriber\Product;

use App\Service\Utils\WebsiteAreaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ProductWithTrashedStatusListener
 */
final class ProductWithTrashedStatusListener implements EventSubscriberInterface
{
    private EntityManagerInterface $manager;

    private WebsiteAreaService $websiteAreaService;

    /**
     * ProductWaitingForAcceptStatusListener constructor.
     *
     * @param EntityManagerInterface $manager
     * @param WebsiteAreaService $websiteAreaService
     */
    public function __construct(EntityManagerInterface $manager, WebsiteAreaService $websiteAreaService)
    {
        $this->manager = $manager;
        $this->websiteAreaService = $websiteAreaService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * @param KernelEvent $event
     */
    public function onKernelRequest(KernelEvent $event): void
    {
        if ($this->websiteAreaService->isAdminArea()) {
            return;
        }

        $this->manager->getFilters()->enable('productWithTrashedStatus');
    }
}
