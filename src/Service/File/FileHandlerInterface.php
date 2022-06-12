<?php

namespace App\Service\File;

use Iterator;

interface FileHandlerInterface
{
    public function read(string $filePath): Iterator;

    /**
     * @param string|array $data
     */
    public function write(string $filePath, $data): void;

    public function close(): void;

    public function support(string $filePath, ?string $presenterModel): bool;

    public static function getPriority(): int;
}
