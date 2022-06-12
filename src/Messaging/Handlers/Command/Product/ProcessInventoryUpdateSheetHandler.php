<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Dictionary\InventoryUpdateDemandStatus;
use App\Dictionary\InventoryUpdateSheetStatus;
use App\Entity\Inventory;
use App\Entity\InventoryUpdateSheet;
use App\Exceptions\Discount\InventoryDiscountRuleViolationException;
use App\Exceptions\Inventory\InventoryPriceRuleViolationException;
use App\Messaging\Messages\Command\Product\ProcessInventoryUpdateSheet;
use App\Repository\InventoryRepository;
use App\Repository\InventoryUpdateSheetRepository;
use App\Service\Product\Seller\InventoryUpdate\Creator\InventoryUpdateDemandCreator;
use App\Service\Product\Seller\InventoryUpdate\InventoryUpdateDemandInitializer;
use App\Service\Product\Seller\InventoryUpdate\InventoryUpdater;
use App\Service\Product\Seller\InventoryUpdate\InventoryUpdateSheetManager;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

class ProcessInventoryUpdateSheetHandler implements MessageHandlerInterface
{
    public function __construct(
        private InventoryUpdateSheetRepository $inventoryUpdateSheetRepository,
        private InventoryUpdateSheetManager $inventoryUpdateSheetManager,
        private InventoryRepository $inventoryRepository,
        private EntityManagerInterface $entityManager,
        private InventoryUpdateDemandCreator $inventoryUpdateDemandCreator,
        private InventoryUpdateDemandInitializer $demandInitializer,
        private InventoryUpdater $inventoryUpdater
    ) {
    }

    public function __invoke(ProcessInventoryUpdateSheet $message): void
    {
        $sheet = $this->inventoryUpdateSheetRepository->find($message->getInventoryUpdateSheetId());

        if (!$sheet) {
            throw new InvalidArgumentException();
        }

        try {
            $sheet->setStatus(InventoryUpdateSheetStatus::PENDING);
            $this->entityManager->flush();
            $iterator = $this->inventoryUpdateSheetManager->getUploadedSpreadsheetIterator($sheet);


            $errors          = [];
            $successfulCount = 0;
            $rows            = [];

            while ($currentRow = $this->inventoryUpdateSheetManager->getRow($iterator)) {
                $rows[$currentRow['inventoryId']] = $currentRow;
                if (count($rows) % 100 !== 0) {
                    continue;
                }

                $successfulCount += $this->updateChunk($sheet, $rows, $errors);

                $rows = [];
            }

            if (count($rows) > 0) {
                $successfulCount += $this->updateChunk($sheet, $rows, $errors);
            }

            $sheet->setFailedCount(count($errors));
            $sheet->setSucceededCount($successfulCount);
            $sheet->setTotalCount(count($errors) + $successfulCount);
            $sheet->setStatus(InventoryUpdateSheetStatus::PROCESSED);

            if (count($errors) > 0) {
                $this->initializeNewDemand($sheet, $errors);
            } else {
                $rootDemand = $sheet->getRootDemand();
                $rootDemand->setStatus(InventoryUpdateDemandStatus::INITIALIZED);
            }
        } catch (Throwable) {
            $sheet->setStatus(InventoryUpdateSheetStatus::FAILED);
        }

        $this->entityManager->flush();
    }

    private function initializeNewDemand(InventoryUpdateSheet $sheet, array $errors): void
    {
        $demand = $this->inventoryUpdateDemandCreator->createChildDemand($sheet);
        $this->entityManager->flush();
        $this->demandInitializer->initializeChildDemand($sheet, $demand, $errors);
        $this->entityManager->flush();
    }

    private function updateChunk(InventoryUpdateSheet $sheet, array $rows, array &$errors): int
    {
        $successfulCount = 0;
        /** @var array|Inventory[] $inventories */
        $inventories = $this->inventoryRepository->findBySellerCodeAndInventoryIds($sheet->getDemand()->getSeller()->getIdentifier(), array_keys($rows));

        foreach ($rows as $inventoryId => $row) {
            if (!isset($inventories[$inventoryId])) {
                $errors[$row['rowIndex']] = 'کد تنوع نامعتبر است.';
                continue;
            }
            $inventory = $inventories[$inventoryId];
            try {
                $this->inventoryUpdater->update($inventory, array_intersect_key($row, array_flip([
                    'isActive',
                    'leadTime',
                    'price',
                    'finalPrice',
                    'sellerStock',
                    'maxPurchasePerOrder',
                    'sellerCode'
                ])));
            } catch (ValidationFailedException $exception) {
                $errors[$row['rowIndex']] = $exception->getViolations()->get(0)->getMessage();
                continue;
            } catch (InventoryDiscountRuleViolationException | InventoryPriceRuleViolationException $exception) {
                $errors[$row['rowIndex']] = $exception->getMessage();
                continue;
            }
            $successfulCount++;
        }

        $this->entityManager->flush();

        return $successfulCount;
    }
}
