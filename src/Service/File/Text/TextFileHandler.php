<?php

namespace App\Service\File\Text;

use App\Service\File\Exceptions\IOException;
use App\Service\File\FileHandlerAbstract;
use App\Service\File\FileType;
use InvalidArgumentException;
use Iterator;

final class TextFileHandler extends FileHandlerAbstract
{
    /**
     * @var resource
     */
    protected $pointer;

    protected Iterator $iterator;

    protected function openAndRead(string $filePath): Iterator
    {
        $this->open($filePath);

        return new TextFileIterator($this->pointer);
    }

    /**
     * @param string $data
     */
    protected function openAndWrite(string $filePath, $data): void
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('You can only add text stream to this file');
        }

        $this->open($filePath);

        fwrite($this->pointer, $data);
    }

    protected function closeFile(): void
    {
        if ($this->pointer) {
            fclose($this->pointer);
        }
    }

    private function open(string $filePath): void
    {
        $this->pointer = fopen($filePath, 'ab+');

        if (!$this->pointer) {
            throw new IOException("Could not open file $filePath.");
        }

        $this->fileIsOpen = true;
    }

    public function support(string $filePath, ?string $presenterModel): bool
    {
        $fileType = pathinfo($filePath, PATHINFO_EXTENSION);

        return $fileType === FileType::TXT;
    }

    public static function getPriority(): int
    {
        return 6;
    }
}
