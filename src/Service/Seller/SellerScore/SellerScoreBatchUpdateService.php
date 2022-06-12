<?php

namespace App\Service\Seller\SellerScore;

use App\Dictionary\FileHandlerPresenterModels;
use App\Messaging\Messages\Command\Log\SellerScoreUpdateMessage;
use App\Repository\SellerRepository;
use App\Service\File\CSV\SellerScore\SellerScoreRow;
use App\Service\File\FileServiceInterface;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tightenco\Collect\Support\LazyCollection;

class SellerScoreBatchUpdateService
{
    private ?Writer $writer;

    private string $failuresFilePath;

    public function __construct(
        protected FileServiceInterface $uploadedFileHandler,
        protected SellerRepository $sellerRepository,
        protected EntityManagerInterface $entityManager,
        protected MessageBusInterface $bus
    ) {
    }

    public function execute(string $filePath): void
    {
        $this->uploadedFileHandler->create($filePath, FileHandlerPresenterModels::SELLER_SCORE);
        $rows = $this->uploadedFileHandler->getRows();

        $this->failuresFilePath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '_failures.xlsx';

        $chunks = $rows->chunk(500);
        foreach ($chunks as $chunk) {
            $this->process($chunk);
        }
    }

    private function process(LazyCollection $chunk)
    {
        $sellerIds = $this->extractSellerIdsFrom($chunk);
        $sellers   = $this->sellerRepository->getByIdsAsAssociatedArray($sellerIds);

        $chunk->each(function (SellerScoreRow $row) use ($sellers) {
            $seller = $sellers[$row->getSellerId()] ?? null;
            if (isset($seller)) {
                $sellerScore = $row->toSellerScoreEntity();
                $seller->setScore($sellerScore);

                $this->bus->dispatch(new SellerScoreUpdateMessage($seller->getId(), $sellerScore));
            } else {
                $this->addToFailures($row, 'seller not found.');
            }
        });

        if (isset($this->writer)) {
            $this->writer->close();
        }

        $this->entityManager->flush();
    }

    private function extractSellerIdsFrom(LazyCollection $chunk): array
    {
        $sellerIds = [];
        $chunk->each(
            function (SellerScoreRow $row) use (&$sellerIds) {
                $sellerIds[] = $row->getSellerId();
            }
        );

        return $sellerIds;
    }

    private function addToFailures(SellerScoreRow $row, string $message): void
    {
        if (!isset($this->writer)) {
            $this->writer = WriterEntityFactory::createXLSXWriter();
            $this->writer->openToFile($this->failuresFilePath);
        }

        $row = WriterEntityFactory::createRowFromArray([$row->getSellerId(), $message]);
        $this->writer->addRow($row);
    }
}
