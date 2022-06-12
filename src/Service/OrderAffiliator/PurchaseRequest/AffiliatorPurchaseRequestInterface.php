<?php

namespace App\Service\OrderAffiliator\PurchaseRequest;

use App\Entity\Order;

interface AffiliatorPurchaseRequestInterface
{
    public function send(Order $order): void;
}
