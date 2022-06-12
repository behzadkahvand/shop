<?php

namespace App\Service\Seller\SellerPackage\Status;

use App\Dictionary\SellerPackageStatus;
use App\Entity\Admin;
use App\Entity\SellerPackage;
use App\Entity\SellerPackageStatusLog;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Service\Seller\SellerPackage\Status\Exceptions\InvalidSellerPackageStatusException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class SellerPackageStatusService
 */
class SellerPackageStatusService
{
    private EntityManagerInterface $em;

    private SellerOrderItemStatusService $orderItemStatusService;

    public function __construct(EntityManagerInterface $em, SellerOrderItemStatusService $orderItemStatusService)
    {
        $this->em = $em;
        $this->orderItemStatusService = $orderItemStatusService;
    }

    public function change(SellerPackage $package, string $nextStatus, ?Admin $admin): void
    {
        if (!SellerPackageStatus::isValid($nextStatus)) {
            throw new InvalidSellerPackageStatusException($nextStatus);
        }

        $currentStatus = $package->getStatus();

        if ($currentStatus === $nextStatus) {
            return;
        }

        $statusLog = new SellerPackageStatusLog();
        $statusLog->setStatusFrom($currentStatus)
                  ->setStatusTo($nextStatus)
                  ->setUser($admin);

        $package->setStatus($nextStatus);
        $package->addStatusLog($statusLog);

        $this->em->persist($statusLog);
        $this->em->flush();
    }
}
