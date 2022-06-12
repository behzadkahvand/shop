<?php

namespace App\Service\OrderShipment;

use App\Entity\Cart;
use App\Entity\CustomerAddress;
use App\Entity\Order;

/**
 * Interface OrderShipmentServiceInterface
 */
interface OrderShipmentServiceInterface
{
    /**
     * @param Cart $cart
     * @param Order $order
     * @param array $shipments
     * @param CustomerAddress $address
     *
     * @return array
     */
    public function getShipmentObjects(Cart $cart, Order $order, array $shipments, CustomerAddress $address): array;
}
