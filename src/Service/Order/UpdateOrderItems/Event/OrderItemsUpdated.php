<?php

namespace App\Service\Order\UpdateOrderItems\Event;

use App\Entity\Admin;
use App\Entity\Order;
use App\Entity\OrderItem;

class OrderItemsUpdated
{
    private Order $order;

    /**
     * @var array<OrderItem>
     */
    private array $updatedOrderItems;

    private Admin $admin;

    public function __construct(Order $order, array $updatedOrderItems, Admin $admin)
    {
        $this->order = $order;
        $this->updatedOrderItems = $updatedOrderItems;
        $this->admin = $admin;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return OrderItem[]
     */
    public function getUpdatedOrderItems(): array
    {
        return $this->updatedOrderItems;
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }
}
