<?php

namespace App\Service\File\Text;

use App\Service\File\FileIteratorInterface;

final class TextFileIterator implements FileIteratorInterface
{
    /**
     * @var resource
     */
    private $filePointer;

    private int $position = 0;

    public function __construct($filePointer)
    {
        $this->filePointer = $filePointer;
    }

    public function current(): Row
    {
        $row = fgets($this->filePointer);

        return Row::fromString($row);
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return ! feof($this->filePointer);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}
