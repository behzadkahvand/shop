<?php

namespace App\Service\File\CSV;

use App\Service\File\FileIteratorInterface;
use App\Service\File\RowAbstract;
use Box\Spout\Reader\IteratorInterface;

abstract class BaseCSVIterator implements FileIteratorInterface
{
    public function __construct(protected IteratorInterface $iterator)
    {
    }

    abstract public function current(): RowAbstract;

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }
}
