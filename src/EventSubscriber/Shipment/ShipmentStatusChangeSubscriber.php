<?php

namespace App\EventSubscriber\Shipment;

use App\Dictionary\TransferReason;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\OrderShipment\OrderShipmentStatus\Events\OrderShipmentStatusChanged;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ShipmentStatusChangeSubscriber implements EventSubscriberInterface
{
    public function __construct(protected OrderWalletPaymentHandler $walletPaymentHandler)
    {
    }

    public static function getSubscribedEvents()
    {
        return [OrderShipmentStatusChanged::class => 'onShipmentCancel'];
    }

    public function onShipmentCancel(OrderShipmentStatusChanged $event)
    {
        $shipment = $event->getOrderShipment();

        if (!$shipment->isCanceled()) {
            return;
        }

        $this->walletPaymentHandler->handle($shipment->getOrder(), TransferReason::ORDER_REFUND);
    }
}
