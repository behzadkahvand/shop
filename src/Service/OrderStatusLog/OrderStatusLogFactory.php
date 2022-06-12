<?php

namespace App\Service\OrderStatusLog;

use App\Entity\OrderStatusLog;

class OrderStatusLogFactory
{
    public function getOrderStatusLog(): OrderStatusLog
    {
        return new OrderStatusLog();
    }
}
