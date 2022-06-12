<?php

namespace App\Service\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvService
{
    /**
     * @return array<string>
     */
    public function getFirstColumnFromUploadedFile(UploadedFile $file): array
    {
        $rows = [];
        if (($handle = fopen($file->getRealPath(), "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, " ")) !== false) {
                if (is_array($data) && count($data) > 0) {
                    $rows[] = $data[0];
                }
            }
            fclose($handle);
        }

        return $rows;
    }
}
