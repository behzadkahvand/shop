<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Exceptions\Product\Import\ProductImportException;
use App\Messaging\Messages\Command\Product\ImportProductFromDigikala;
use App\Repository\SellerRepository;
use App\Service\Product\Import\Digikala\ImportDigikalaProductService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ImportProductFromDigikalaHandler implements MessageHandlerInterface
{
    public function __construct(
        protected ImportDigikalaProductService $importDigikalaProductService,
        protected SellerRepository $sellerRepository,
        protected LoggerInterface $logger
    ) {
    }

    public function __invoke(ImportProductFromDigikala $message): void
    {
        $this->logger->debug('Handling ImportProductFromDigikala message with dkp: ' . $message->getDigikalaDkp());

        $seller = $message->getSellerId() ? $this->sellerRepository->find($message->getSellerId()) : null;

        try {
            $this->importDigikalaProductService->import(
                $message->getDigikalaDkp(),
                $seller,
                $message->getDigikalaSellerId()
            );
        } catch (ProductImportException) {
        }
    }
}
