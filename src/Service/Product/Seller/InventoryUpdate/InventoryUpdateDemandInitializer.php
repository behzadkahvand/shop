<?php

namespace App\Service\Product\Seller\InventoryUpdate;

use App\Dictionary\InventoryUpdateDemandStatus;
use App\Entity\InventoryUpdateDemand;
use App\Entity\InventoryUpdateSheet;
use App\Repository\InventoryRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class InventoryUpdateDemandInitializer
{
    private InventoryRepository $inventoryRepository;
    private Filesystem $filesystem;
    private InventoryUpdateSheetManager $sheetManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        InventoryUpdateSheetManager $sheetManager,
        InventoryRepository $inventoryRepository,
        Filesystem $filesystem,
        EntityManagerInterface $entityManager
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->filesystem = $filesystem;
        $this->sheetManager = $sheetManager;
        $this->entityManager = $entityManager;
    }

    public function initialize(InventoryUpdateDemand $demand)
    {
        $spreadsheet = $this->sheetManager->getNewSheetFile();
        $inventoryIterator = $this->inventoryRepository->iteratorForSeller($demand->getSeller());
        $demand->setStatus(InventoryUpdateDemandStatus::INITIALIZING);
        $this->entityManager->flush();

        foreach ($inventoryIterator as $index => $inventory) {
            $this->sheetManager->addInventoryToSheetFile($spreadsheet, $index + 1, $inventory);
        }

        $savedPath = $this->sheetManager->saveInitializedSheet($spreadsheet, $demand);

        $demand->setStatus(InventoryUpdateDemandStatus::INITIALIZED);

        $pathInfo = pathinfo($savedPath);
        $demand->setDirPath($pathInfo['dirname']);
        $demand->setFileName($pathInfo['basename']);
        $demand->setExpiresAt(new DateTime('+1 day'));
    }

    public function initializeChildDemand(
        InventoryUpdateSheet $inventoryUpdateSheet,
        InventoryUpdateDemand $inventoryUpdateDemand,
        array $errors
    ) {
        $newSpreadsheet = $this->sheetManager->getNewSheetFile();
        $uploadedSheet = $this->sheetManager->getUploadedSpreadsheet($inventoryUpdateSheet);

        $rowCount = 2;
        foreach ($errors as $rowIndexInUploadedSheet => $error) {
            $this->sheetManager->copyRowWithComment(
                $uploadedSheet,
                $rowIndexInUploadedSheet,
                $newSpreadsheet,
                $rowCount,
                $error
            );
            $rowCount++;
        }
        $savedPath = $this->sheetManager->saveInitializedSheet($newSpreadsheet, $inventoryUpdateDemand);
        $inventoryUpdateDemand->setStatus(InventoryUpdateDemandStatus::INITIALIZED);
        $pathInfo = pathinfo($savedPath);
        $inventoryUpdateDemand->setDirPath($pathInfo['dirname']);
        $inventoryUpdateDemand->setFileName($pathInfo['basename']);
        $inventoryUpdateDemand->setExpiresAt(new DateTime('+1 day'));
    }
}
