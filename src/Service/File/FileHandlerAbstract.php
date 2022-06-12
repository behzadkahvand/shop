<?php

namespace App\Service\File;

use App\Service\File\Exceptions\IOException;
use Iterator;
use Throwable;

abstract class FileHandlerAbstract implements FileHandlerInterface
{
    protected bool $fileIsOpen = false;

    abstract protected function openAndRead(string $filePath): Iterator;

    abstract protected function openAndWrite(string $filePath, $data);

    abstract protected function closeFile(): void;

    abstract public function support(string $filePath, ?string $presenterModel): bool;

    abstract public static function getPriority(): int;

    public function read(string $filePath): Iterator
    {
        $directory = dirname($filePath);

        if (! is_dir($directory) || (! is_writable($directory))) {
            throw new IOException('The file path in not valid: ' . $filePath);
        }

        try {
            return $this->openAndRead($filePath);
        } catch (Throwable $exception) {
            throw new IOException("Could not open $filePath for reading! ({$exception->getMessage()})");
        }
    }

    public function write(string $filePath, $data): void
    {
        try {
            $this->openAndWrite($filePath, $data);
        } catch (Throwable $e) {
            $this->closeAndAttemptToCleanupAllFiles($filePath);

            throw $e;
        }
    }

    public function close(): void
    {
        if ($this->fileIsOpen) {
            $this->closeFile();
            $this->fileIsOpen = false;
        }
    }

    private function closeAndAttemptToCleanupAllFiles(string $filePath): void
    {
        $this->close();

        // Remove output file if it was created
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
