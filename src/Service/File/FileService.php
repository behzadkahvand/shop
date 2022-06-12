<?php

namespace App\Service\File;

use App\Service\File\Text\TextFileHandler;
use Tightenco\Collect\Support\LazyCollection;

final class FileService implements FileServiceInterface
{
    private string $filePath;

    private FileHandlerInterface $handler;

    private bool $processHeader = true;

    private bool $processingFirstRow = true;

    private FileHandlerFactory $factory;

    public function __construct(FileHandlerFactory $factory)
    {
        $this->factory = $factory;
    }

    public function create(string $filePath, ?string $presenterModelName = null): self
    {
        $this->filePath = $filePath;
        $this->handler = $this->factory->create($filePath, $presenterModelName);

        return $this;
    }

    public function getRows(): LazyCollection
    {
        return LazyCollection::make(function () {
            $rowIterator = $this->handler->read($this->filePath);

            while ($rowIterator->valid()) {
                yield $rowIterator->current();

                $rowIterator->next();
            }

            $this->handler->close();
        });
    }

    public function addRow($row): void
    {
        if (!$this->handler instanceof TextFileHandler && $this->processHeader && $this->processingFirstRow) {
            $headerValues = array_keys($row);
            $this->handler->write($this->filePath, $headerValues);
            $this->processingFirstRow = false;
        }

        $this->handler->write($this->filePath, $row);
    }

    public function noHeaderRow(): self
    {
        $this->processHeader = false;

        return $this;
    }
}
