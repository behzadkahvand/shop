<?php

namespace App\Service\File;

interface FileServiceInterface
{
    public function create(string $filePath, ?string $presenterModelName = null): self;

    /**
     * @return \Tightenco\Collect\Support\LazyCollection
     */
    public function getRows();

    /**
     * @param string|array $row
     */
    public function addRow($row): void;

    public function noHeaderRow(): self;
}
