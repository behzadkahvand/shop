<?php

namespace App\Service\Order\DeleteOrderItem\Event;

use App\Entity\Admin;
use App\Entity\Order;
use App\Entity\OrderItem;

class OrderItemRemoved
{
    private OrderItem $orderItem;

    private Order $order;

    private Admin $admin;

    public function __construct(OrderItem $orderItem, Order $order, Admin $admin)
    {
        $this->orderItem = $orderItem;
        $this->order = $order;
        $this->admin = $admin;
    }

    public function getOrderItem(): OrderItem
    {
        return $this->orderItem;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }
}
