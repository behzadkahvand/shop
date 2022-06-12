<?php

namespace App\Service\Product\Seller\InventoryUpdate\Creator;

use App\Dictionary\InventoryUpdateDemandStatus;
use App\Entity\InventoryUpdateDemand;
use App\Entity\InventoryUpdateSheet;
use App\Entity\Seller;
use App\Repository\InventoryUpdateDemandRepository;
use App\Service\Product\Seller\InventoryUpdate\Exception\NotClosedDemandExists;
use Doctrine\ORM\EntityManagerInterface;

class InventoryUpdateDemandCreator
{
    private EntityManagerInterface $entityManager;

    private InventoryUpdateDemandRepository $repository;

    public function __construct(EntityManagerInterface $entityManager, InventoryUpdateDemandRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function create(Seller $seller)
    {
        $inventoryUpdateDemand = new InventoryUpdateDemand();
        $inventoryUpdateDemand->setSeller($seller);

        $this->entityManager->persist($inventoryUpdateDemand);

        return $inventoryUpdateDemand;
    }

    public function createChildDemand(InventoryUpdateSheet $inventoryUpdateSheet): InventoryUpdateDemand
    {
        $previousPendingDemand = $this->repository->findOneBy([
            'status' => InventoryUpdateDemandStatus::PENDING,
            'demand' => $inventoryUpdateSheet->getDemand()
        ]);

        if ($previousPendingDemand) {
            throw new NotClosedDemandExists(sprintf('There is some not completed demand for the store'));
        }

        $inventoryUpdateDemand = new InventoryUpdateDemand();
        $inventoryUpdateDemand->setSeller($inventoryUpdateSheet->getDemand()->getSeller());
        $inventoryUpdateDemand->setDemand($inventoryUpdateSheet->getDemand());
        $inventoryUpdateSheet->setFixerDemand($inventoryUpdateDemand);

        $this->entityManager->persist($inventoryUpdateDemand);

        return $inventoryUpdateDemand;
    }
}
