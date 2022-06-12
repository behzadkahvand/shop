<?php

namespace App\Service\OrderShipment\Adapters;

use App\Entity\Cart;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use App\Repository\ShippingPeriodRepository;
use App\Service\OrderShipment\OrderShipmentServiceInterface;
use App\Service\PartialShipment\PartialShipmentService;
use App\Service\PartialShipment\ValueObject\BaseFreezedPartialShipment;

/**
 * Class PartialShipmentToOrderShipmentAdapter
 */
final class PartialShipmentToOrderShipmentAdapter implements OrderShipmentServiceInterface
{
    /**
     * @var PartialShipmentService
     */
    private PartialShipmentService $partialShipmentService;

    /**
     * PartialShipmentToOrderShipmentAdapter constructor.
     *
     * @param PartialShipmentService $partialShipmentService
     */
    public function __construct(PartialShipmentService $partialShipmentService)
    {
        $this->partialShipmentService = $partialShipmentService;
    }

    /**
     * @inheritDoc
     */
    public function getShipmentObjects(Cart $cart, Order $order, array $shipments, CustomerAddress $address): array
    {
        return array_map(
            static fn(BaseFreezedPartialShipment $partialShipment) => $partialShipment->toOrderShipment($order),
            $this->partialShipmentService->getPartialShipments(
                $cart,
                $address,
                $shipments,
                $address->getCity()->isExpress()
            )
        );
    }
}
