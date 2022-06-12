<?php

namespace App\Service\Product\Campaign;

use App\Entity\Inventory;
use App\Exceptions\Product\Campaign\InvalidCampaignRequestException;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class BlackFridayBatchUpdateService
{
    public function __construct(
        private Xlsx $xlsxReader,
        private BlackFridayRuleService $blackFridayRuleService,
        private InventoryRepository $inventoryRepository,
        private string $blackFridayFileTemplate,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function execute(string $excelFilePath)
    {
        $sheet        = $this->xlsxReader->load($excelFilePath);
        $iterator     = $sheet->getActiveSheet()->getRowIterator(2);
        $lastRowIndex = $sheet->getActiveSheet()->getHighestDataRow();

        $failureSheet = $this->getNewSheetFile();
        $writer       = $this->createWriter($failureSheet);

        $failureSheetRowIndex = 2;
        while (true) {
            $row = $this->readCurrentRow($iterator);
            try {
                $inventory = $this->findInventory($row['inventoryId']);
                $this->blackFridayRuleService->apply(
                    $inventory,
                    new BlackFridayRequest(
                        $row['finalPrice'],
                        $row['sellerStock']
                    )
                );
            } catch (Exception $e) {
                $this->addToFailures($sheet, $row['rowIndex'], $failureSheet, $failureSheetRowIndex, $e->getMessage());
                $failureSheetRowIndex++;
            }

            $this->entityManager->clear();

            if ($lastRowIndex <= $row['rowIndex']) {
                break;
            }
            $iterator->next();
        }

        if ($this->hasError($failureSheet)) {
            $filePath = pathinfo($excelFilePath, PATHINFO_DIRNAME);
            $fileName = pathinfo($excelFilePath, PATHINFO_FILENAME);
            $writer->save(sprintf('%s/%s-failures.%s', $filePath, $fileName, 'xlsx'));
        }
    }

    protected function getNewSheetFile(): Spreadsheet
    {
        return $this->xlsxReader->load($this->blackFridayFileTemplate);
    }

    protected function addToFailures(
        Spreadsheet $uploadedSheet,
        int $rowIndexInUploadedSheet,
        Spreadsheet $failureSheet,
        int $rowIndexInNewSpreadsheet,
        $message
    ) {
        foreach (["A", "B", "C", "D", "C", "E", "F", "G", "H"] as $column) {
            $failureSheet->getActiveSheet()->setCellValue(
                "{$column}{$rowIndexInNewSpreadsheet}",
                $uploadedSheet->getActiveSheet()->getCell("{$column}{$rowIndexInUploadedSheet}")->getValue()
            );
        }

        $failureSheet->getActiveSheet()->setCellValue("I{$rowIndexInNewSpreadsheet}", $message);
    }

    /**
     * @throws InvalidCampaignRequestException
     */
    private function findInventory(?int $inventoryId): Inventory
    {
        if (null === $inventoryId) {
            throw new InvalidCampaignRequestException('inventory not found');
        }

        $inventory = $this->inventoryRepository->find($inventoryId);
        if (!isset($inventory)) {
            throw new InvalidCampaignRequestException('inventory not found');
        }

        return $inventory;
    }

    private function createWriter(Spreadsheet $failureSheet): XlsxWriter
    {
        return new XlsxWriter($failureSheet);
    }

    private function hasError(Spreadsheet $failureSheet): bool
    {
        return $failureSheet->getActiveSheet()->getHighestDataRow() > 1;
    }

    private function readCurrentRow(RowIterator $iterator): array
    {
        $current   = $iterator->current();
        $worksheet = $current->getWorksheet();
        $rowIndex  = $current->getRowIndex();

        return [
            'rowIndex'    => $rowIndex,
            'inventoryId' => $worksheet->getCell("D{$rowIndex}")->getValue(),
            'finalPrice'  => $worksheet->getCell("G{$rowIndex}")->getValue(),
            'sellerStock' => $worksheet->getCell("H{$rowIndex}")->getValue(),
        ];
    }
}
