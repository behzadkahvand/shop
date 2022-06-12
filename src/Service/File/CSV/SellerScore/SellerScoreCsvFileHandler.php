<?php

namespace App\Service\File\CSV\SellerScore;

use App\Dictionary\FileHandlerPresenterModels;
use App\Service\File\CSV\BaseCSVFileHandler;
use App\Service\File\FileType;
use Box\Spout\Reader\IteratorInterface;
use Iterator;

class SellerScoreCsvFileHandler extends BaseCSVFileHandler
{
    protected function getResultIterator(IteratorInterface $iterator): Iterator
    {
        return new SellerScoreCsvFileIterator($iterator);
    }

    public function support(string $filePath, ?string $presenterModel): bool
    {
        $fileType = pathinfo($filePath, PATHINFO_EXTENSION);

        return in_array($fileType, [FileType::XLSX, FileType::CSV], true) &&
               $presenterModel === FileHandlerPresenterModels::SELLER_SCORE;
    }

    public static function getPriority(): int
    {
        return 1;
    }
}
