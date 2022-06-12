<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\ImportDigikalaSellerProducts;
use App\Service\Product\Import\Digikala\ImportDigikalaSellerProductsService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ImportDigikalaSellerProductsHandler implements MessageHandlerInterface
{
    public function __construct(protected ImportDigikalaSellerProductsService $importer)
    {
    }

    public function __invoke(ImportDigikalaSellerProducts $message): void
    {
        $this->importer->import(
            $message->getSellerId(),
            $message->getDigikalaSellerId()
        );
    }
}
