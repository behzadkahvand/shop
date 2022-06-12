<?php

namespace App\Service\File\CSV;

use App\Service\File\FileHandlerAbstract;
use App\Service\File\Exceptions\InvalidSheetStructure;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\WriterInterface;
use InvalidArgumentException;
use Iterator;

abstract class BaseCSVFileHandler extends FileHandlerAbstract
{
    private ReaderInterface $reader;

    private WriterInterface $writer;

    private int $numberOfRows = 0;

    protected function openAndRead(string $filePath): Iterator
    {
        $this->reader = ReaderEntityFactory::createReaderFromFile($filePath);
        $this->reader->open($filePath);

        $this->fileIsOpen = true;

        $this->reader->getSheetIterator()->rewind();

        $sheet = $this->reader->getSheetIterator()->current();

        /** @var  \Box\Spout\Reader\CSV\RowIterator $iterator */
        $iterator = $sheet->getRowIterator();

        $iterator->rewind();

        // File is empty
        if (! $iterator->valid()) {
            throw new InvalidSheetStructure();
        }

        // Skip header row
        $iterator->next();

        return $this->getResultIterator($iterator);
    }

    abstract protected function getResultIterator(IteratorInterface $iterator): Iterator;

    /**
     * @param array $data
     */
    protected function openAndWrite(string $filePath, $data): void
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('You can only add arrays of data to this file');
        }

        if (! $this->fileIsOpen) {
            $this->writer = WriterEntityFactory::createWriterFromFile($filePath);
            $this->writer->openToFile($filePath);
            $this->fileIsOpen = true;
        }

        $row = WriterEntityFactory::createRowFromArray($data);
        $this->writer->addRow($row);
        $this->numberOfRows++;
    }

    protected function closeFile(): void
    {
        if ($this->reader) {
            $this->reader->close();
        }

//        if ($this->writer) {
//            $this->writer->close();
//        }
    }
}
