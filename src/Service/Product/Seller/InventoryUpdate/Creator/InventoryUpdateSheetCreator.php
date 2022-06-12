<?php

namespace App\Service\Product\Seller\InventoryUpdate\Creator;

use App\Dictionary\InventoryUpdateDemandStatus;
use App\Entity\InventoryUpdateDemand;
use App\Entity\InventoryUpdateSheet;
use App\Entity\Seller;
use App\Repository\InventoryUpdateDemandRepository;
use App\Service\Product\Seller\InventoryUpdate\Exception\DemandIsExpired;
use App\Service\Product\Seller\InventoryUpdate\Exception\InvalidSheetFile;
use App\Service\Product\Seller\InventoryUpdate\Exception\NotClosedDemandExists;
use App\Service\Product\Seller\InventoryUpdate\Exception\NotProcessableDemandStatus;
use App\Service\Product\Seller\InventoryUpdate\InventoryUpdateSheetManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class InventoryUpdateSheetCreator
{
    private EntityManagerInterface $entityManager;
    private InventoryUpdateDemandRepository $repository;
    private InventoryUpdateSheetManager $inventoryUpdateSheetManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        InventoryUpdateDemandRepository $repository,
        InventoryUpdateSheetManager $inventoryUpdateSheetManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->inventoryUpdateSheetManager = $inventoryUpdateSheetManager;
    }

    public function create(UploadedFile $file)
    {
        $demand = $this->repository->findOneBy(['fileName' => $file->getClientOriginalName()]);

        if (!$demand) {
            throw new InvalidSheetFile();
        }

        if ($demand->isExpired()) {
            throw new DemandIsExpired();
        }

        if ($demand->getStatus() !== InventoryUpdateDemandStatus::INITIALIZED) {
            throw new NotProcessableDemandStatus();
        }

        $inventoryUpdateSheet = new InventoryUpdateSheet($demand);
        $inventoryUpdateSheet->setFileName($demand->getFileName());
        $this->entityManager->persist($inventoryUpdateSheet);
        $savedPath = $this->inventoryUpdateSheetManager->saveUploadedSheet($file, $inventoryUpdateSheet);
        $savedPathInfo = pathinfo($savedPath);
        $inventoryUpdateSheet->setDirPath($savedPathInfo['dirname']);

        return $inventoryUpdateSheet;
    }
}
