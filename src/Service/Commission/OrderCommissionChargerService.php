<?php

namespace App\Service\Commission;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\CampaignCommission;
use App\Repository\CampaignCommissionRepository;

class OrderCommissionChargerService
{
    public function __construct(private CampaignCommissionRepository $campaignCommissionRepository)
    {
    }

    public function charge(Order $order): void
    {
        foreach ($order->getItems() as $orderItem) {
            $commissionFee = $this->calculateCommissionFee($orderItem);
            $orderItem->setCommission($commissionFee);
        }
    }

    private function calculateCommissionFee(OrderItem $orderItem): float
    {
        $campaignCommission = $this->findCampaignCommission($orderItem);
        if (isset($campaignCommission)) {
            return $campaignCommission->getFee();
        }

        return $orderItem->getCategory()->getCommission();
    }

    private function findCampaignCommission(OrderItem $orderItem): ?CampaignCommission
    {
        return $this->campaignCommissionRepository->findActiveCommission(
            $orderItem->getCategory(),
            $orderItem->getBrand(),
            $orderItem->getSeller()
        );
    }
}
