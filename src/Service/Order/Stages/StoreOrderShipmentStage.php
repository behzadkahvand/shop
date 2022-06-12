<?php

namespace App\Service\Order\Stages;

use App\Entity\OrderShipment;
use App\Service\OrderShipment\OrderShipmentServiceInterface;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\TagAwarePipelineStageInterface;

class StoreOrderShipmentStage implements TagAwarePipelineStageInterface
{
    public function __construct(
        protected OrderShipmentServiceInterface $orderShipmentService,
    ) {
    }

    public function __invoke(AbstractPipelinePayload $payload)
    {
        $cart            = $payload->getCart();
        $customerAddress = $payload->getCustomerAddress();
        $order           = $payload->getOrder();

        $shipments = $this->orderShipmentService->getShipmentObjects(
            $cart,
            $order,
            $payload->getSelectedShipments(),
            $customerAddress
        );

        collect($shipments)->each(function (OrderShipment $orderShipment) use ($order) {
            $order
                ->addShipment($orderShipment)
                ->setSubtotal($order->getSubtotal() + $orderShipment->getSubTotal())
                ->setGrandtotal($order->getGrandtotal() + $orderShipment->getGrandTotal());

            return true;
        });

        $order->getOrderDocument()->setAmount($order->getGrandTotal());

        return $payload;
    }

    public static function getPriority(): int
    {
        return 87;
    }

    public static function getTag(): string
    {
        return 'app.pipeline_stage.order_processing';
    }
}
