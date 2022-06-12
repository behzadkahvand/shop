<?php

namespace App\Service\File\CSV;

use App\Dictionary\FileHandlerPresenterModels;
use App\Service\File\FileType;
use Box\Spout\Reader\IteratorInterface;
use Iterator;

final class TrackingCodeCSVFileHandler extends BaseCSVFileHandler
{
    protected function getResultIterator(IteratorInterface $iterator): Iterator
    {
        return new TrackingCodeCSVFileIterator($iterator);
    }

    public function support(string $filePath, ?string $presenterModel): bool
    {
        $fileType = pathinfo($filePath, PATHINFO_EXTENSION);

        return in_array($fileType, [FileType::XLSX, FileType::CSV], true) &&
            $presenterModel === FileHandlerPresenterModels::TRACKING_CODE;
    }

    public static function getPriority(): int
    {
        return 7;
    }
}
