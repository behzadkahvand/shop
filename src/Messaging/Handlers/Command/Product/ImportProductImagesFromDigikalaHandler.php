<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\ImportProductImagesFromDigikala;
use App\Repository\ProductRepository;
use App\Service\Product\Update\OutsourceProductUpdateService;
use App\Service\Product\Update\PropertyUpdaters\PropertyUpdaterFactory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ImportProductImagesFromDigikalaHandler implements MessageHandlerInterface
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected OutsourceProductUpdateService $outsourceProductUpdateService,
        protected PropertyUpdaterFactory $updaterFactory
    ) {
    }

    public function __invoke(ImportProductImagesFromDigikala $message): void
    {
        $product = $this->productRepository->find($message->getProductId());

        $this->outsourceProductUpdateService->update(
            $product,
            $this->updaterFactory->makeImageUpdater()
        );
    }
}
