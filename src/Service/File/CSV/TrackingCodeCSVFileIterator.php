<?php

namespace App\Service\File\CSV;

use App\Dictionary\TrackingCodeSheetStructure;
use App\Service\File\Exceptions\InvalidSheetStructure;

final class TrackingCodeCSVFileIterator extends BaseCSVIterator
{
    public function current(): TrackingCodeRow
    {
        $row = $this->iterator->current();

        // Row is invalid
        if ($row->getNumCells() !== count(TrackingCodeSheetStructure::toArray())) {
            throw new InvalidSheetStructure();
        }

        $lineNumber = $row->getCells()[0]->getValue();
        $orderShipmentId = $row->getCells()[1]->getValue();
        $trackingCode = $row->getCells()[2]->getValue();

        return TrackingCodeRow::fromString($lineNumber, $orderShipmentId, $trackingCode);
    }
}
