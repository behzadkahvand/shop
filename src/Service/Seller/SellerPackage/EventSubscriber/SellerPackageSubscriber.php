<?php

namespace App\Service\Seller\SellerPackage\EventSubscriber;

use App\Service\Seller\SellerPackage\Events\SellerOrderItemStatusChangeEvent;
use App\Service\Seller\SellerPackage\Status\SellerPackageStatusFactory;
use App\Service\Seller\SellerPackage\Status\SellerPackageStatusService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SellerPackageSubscriber implements EventSubscriberInterface
{
    private SellerPackageStatusService $sellerPackageStatusService;

    private SellerPackageStatusFactory $sellerPackageStatusFactory;

    public function __construct(
        SellerPackageStatusService $sellerPackageStatusService,
        SellerPackageStatusFactory $sellerPackageStatusFactory
    ) {
        $this->sellerPackageStatusService = $sellerPackageStatusService;
        $this->sellerPackageStatusFactory = $sellerPackageStatusFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SellerOrderItemStatusChangeEvent::class => 'onSellerOrderItemStatusChange',
        ];
    }

    public function onSellerOrderItemStatusChange(SellerOrderItemStatusChangeEvent $event): void
    {
        $sellerOrderItem   = $event->getSellerOrderItem();
        $sellerPackageItem = $sellerOrderItem->getPackageItem();

        if (empty($sellerPackageItem)) {
            return;
        }

        try {
            $sellerPackage = $sellerPackageItem->getPackage();
            $nextStatus    = $this->sellerPackageStatusFactory->create($sellerPackage);
            $this->sellerPackageStatusService->change($sellerPackage, $nextStatus->getName(), null);
        } catch (\Throwable $exception) {
            return;
        }
    }
}
