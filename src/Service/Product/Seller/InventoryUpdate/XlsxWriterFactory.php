<?php

namespace App\Service\Product\Seller\InventoryUpdate;

class XlsxWriterFactory
{
    public function create($spreadsheet)
    {
        return \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
    }
}
