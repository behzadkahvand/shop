<?php

namespace App\DataFixtures;

use App\Entity\OrderCancelReason;

class OrderCancelReasonFixtures extends BaseFixture
{
    protected function loadData(): void
    {
        $orderCancelReason = new OrderCancelReason();
        $orderCancelReason
            ->setCode('cancel_reason_1')
            ->setReason('Order is canceled!');

        $this->manager->persist($orderCancelReason);
        $this->manager->flush();
    }
}
