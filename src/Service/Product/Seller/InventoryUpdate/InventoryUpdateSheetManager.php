<?php

namespace App\Service\Product\Seller\InventoryUpdate;

use App\Entity\Inventory;
use App\Entity\InventoryUpdateDemand;
use App\Entity\InventoryUpdateSheet;
use App\Service\Utils\JalaliCalender;
use DateTime;
use DateTimeZone;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;

class InventoryUpdateSheetManager
{
    private string $templateFilePath;
    private Xlsx $xlsxReader;
    private string $initializedDirPathPrefix;
    private string $uploadedDirPathPrefix;
    private XlsxWriterFactory $xlsxWriterFactory;
    private string $publicDir;
    private Filesystem $filesystem;

    public function __construct(
        string $templateFilePath,
        string $initializedDirPathPrefix,
        string $uploadedDirPathPrefix,
        string $publicDir,
        Xlsx $xlsxReader,
        XlsxWriterFactory $xlsxWriterFactory,
        Filesystem $filesystem
    ) {
        $this->templateFilePath = $templateFilePath;
        $this->xlsxReader = $xlsxReader;
        $this->initializedDirPathPrefix = $initializedDirPathPrefix;
        $this->xlsxWriterFactory = $xlsxWriterFactory;
        $this->publicDir = $publicDir;
        $this->filesystem = $filesystem;
        $this->uploadedDirPathPrefix = $uploadedDirPathPrefix;
    }

    public function getNewSheetFile()
    {
        return $this->xlsxReader->load($this->templateFilePath);
    }

    public function getInitializedSheetPath(InventoryUpdateDemand $demand): string
    {
        return $this->publicDir . '/' . $demand->getDirPath() . '/' . $demand->getFileName();
    }

    public function getUploadedSpreadsheetPath(InventoryUpdateSheet $inventoryUpdateSheet): string
    {
        return $this->publicDir . '/' . $inventoryUpdateSheet->getDirPath() . '/' . $inventoryUpdateSheet->getFileName();
    }

    public function getUploadedSpreadsheet(InventoryUpdateSheet $inventoryUpdateSheet)
    {
        return $this->xlsxReader->load($this->getUploadedSpreadsheetPath($inventoryUpdateSheet));
    }

    public function getUploadedSpreadsheetIterator(InventoryUpdateSheet $inventoryUpdateSheet)
    {
        $spreadsheet = $this->getUploadedSpreadsheet($inventoryUpdateSheet);
        $spreadsheet->setActiveSheetIndex(1);

        return $spreadsheet->getActiveSheet()->getRowIterator(2);
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param int $row
     * @param Inventory $inventory
     */
    public function addInventoryToSheetFile($spreadsheet, $row, Inventory $inventory)
    {
        Assert::minCount($spreadsheet->getAllSheets(), 2);
        $activeSheetIndex = $spreadsheet->getActiveSheetIndex();
        $spreadsheet->setActiveSheetIndex(1);
        $rowNum = $row + 1;
        $variant     = $inventory->getVariant();
        $product     = $variant->getProduct();
        $activeSheet = $spreadsheet->getActiveSheet();

        $values      = [
            "A{$rowNum}" => $product->getCategory()->getTitle(),
            "B{$rowNum}" => $variant->getTitle(),
            "C{$rowNum}" => $product->getId(),
            "D{$rowNum}" => $inventory->getId(),
            "E{$rowNum}" => $inventory->getSeller()->getIdentifier(),
            "F{$rowNum}" => $inventory->getIsActive() ? '1' : '0',
            "G{$rowNum}" => $inventory->getLeadTime(),
            "H{$rowNum}" => $inventory->getPrice(),
            "I{$rowNum}" => $inventory->getFinalPrice(),
            "J{$rowNum}" => $inventory->getSellerStock(),
            "K{$rowNum}" => $inventory->getMaxPurchasePerOrder(),
            "L{$rowNum}" => $inventory->getSellerCode(),
        ];

        foreach ($values as $cellCoordinate => $cellValue) {
            $activeSheet->setCellValue($cellCoordinate, $cellValue);
        }

        $spreadsheet->setActiveSheetIndex($activeSheetIndex);
    }

    public function saveInitializedSheet(Spreadsheet $spreadsheet, InventoryUpdateDemand $inventoryUpdateDemand): string
    {
        $createdAt = $inventoryUpdateDemand->getCreatedAt();
        $jalaliCreatedAt = JalaliCalender::toJalali(
            $createdAt->format('Y'),
            $createdAt->format('m'),
            $createdAt->format('d')
        );

        $jalaliCreatedAt = implode('_', explode('/', $jalaliCreatedAt));
        $time = (new DateTime())->setTimezone(new DateTimeZone('Asia/Tehran'))->format('H_i_s');

        $fileName =
            "ProductList_{$inventoryUpdateDemand->getSeller()->getIdentifier()}_{$jalaliCreatedAt}_{$time}" . '.xlsx'
        ;
        $tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
        $writer = $this->xlsxWriterFactory->create($spreadsheet);
        $writer->save($tempFilePath);

        $initializedSheetFileDirPath = "{$this->initializedDirPathPrefix}/{$inventoryUpdateDemand->getId()}";
        $this->filesystem->mkdir("{$this->publicDir}/$initializedSheetFileDirPath");
        $this->filesystem->copy(
            $tempFilePath,
            "{$this->publicDir}/$initializedSheetFileDirPath/$fileName"
        );
        $this->filesystem->remove($tempFilePath);

        return "$initializedSheetFileDirPath/$fileName";
    }

    public function saveUploadedSheet(UploadedFile $file, InventoryUpdateSheet $inventoryUpdateSheet)
    {
        $uploadedSheetFileDirPath = "{$this->uploadedDirPathPrefix}/{$inventoryUpdateSheet->getDemand()->getId()}";
        $this->filesystem->mkdir("{$this->publicDir}/$uploadedSheetFileDirPath");
        $file->move(
            "$this->publicDir/$uploadedSheetFileDirPath",
            $inventoryUpdateSheet->getDemand()->getFileName()
        );

        return "$uploadedSheetFileDirPath/{$inventoryUpdateSheet->getDemand()->getFileName()}";
    }

    public function getRow(RowIterator $iterator)
    {
        if (!$iterator->valid()) {
            return null;
        }

        $current = $iterator->current();
        $iterator->next();

        if ($current->getWorksheet()->getHighestDataRow() < $current->getRowIndex()) {
            return null;
        }

        $worksheet = $current->getWorksheet();
        $rowIndex = $current->getRowIndex();

        return [
            'rowIndex' => $rowIndex,
            'inventoryId' => $worksheet->getCell("D{$rowIndex}")->getValue(),
            'sellerCode' => $worksheet->getCell("L{$rowIndex}")->getValue(),
            'isActive' => $worksheet->getCell("F{$rowIndex}")->getValue(),
            'leadTime' => $worksheet->getCell("G{$rowIndex}")->getValue(),
            'price' => $worksheet->getCell("H{$rowIndex}")->getValue(),
            'finalPrice' => $worksheet->getCell("I{$rowIndex}")->getValue(),
            'sellerStock' => $worksheet->getCell("J{$rowIndex}")->getValue(),
            'maxPurchasePerOrder' => $worksheet->getCell("K{$rowIndex}")->getValue(),
        ];
    }

    public function copyRowWithComment(
        Spreadsheet $uploadedSheet,
        int $rowIndexInUploadedSheet,
        Spreadsheet $newSpreadsheet,
        int $rowIndexInNewSpreadsheet,
        $comment
    ) {
        $newSpreadsheet->setActiveSheetIndex(1);
        $uploadedSheet->setActiveSheetIndex(1);

        foreach (["A", "B", "C", "D", "C", "E", "F", "G", "H", "I", "J", "K"] as $column) {
            $newSpreadsheet->getActiveSheet()->setCellValue(
                "{$column}{$rowIndexInNewSpreadsheet}",
                $uploadedSheet->getActiveSheet()->getCell("{$column}{$rowIndexInUploadedSheet}")->getValue()
            );
        }

        $newSpreadsheet->getActiveSheet()->setCellValue("L{$rowIndexInNewSpreadsheet}", $comment);
    }
}
