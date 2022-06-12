<?php

namespace App\EventSubscriber;

use App\Dictionary\WebsiteAreaDictionary;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class WebsiteAreaSubscriber
 */
final class WebsiteAreaSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 11]];
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request  = $event->getRequest();
        $pathInfo = $request->getPathInfo();
        $area     = WebsiteAreaDictionary::AREA_CUSTOMER;

        if (0 === strpos($pathInfo, '/admin/')) {
            $area = WebsiteAreaDictionary::AREA_ADMIN;
        }

        if (0 === strpos($pathInfo, '/seller/')) {
            $area = WebsiteAreaDictionary::AREA_SELLER;
        }

        $request->attributes->set('website_area', $area);
    }
}
