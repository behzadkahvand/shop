<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\BatchImportDigikalaProducts;
use App\Service\Product\Import\Digikala\BatchImportDigikalaProductsService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class BatchImportDigikalaProductsHandler implements MessageHandlerInterface
{
    public function __construct(protected BatchImportDigikalaProductsService $importer)
    {
    }

    public function __invoke(BatchImportDigikalaProducts $message): void
    {
        $this->importer->import(
            $message->getUrl(),
            $message->getSellerId(),
            $message->getDigikalaSellerId(),
        );
    }
}
