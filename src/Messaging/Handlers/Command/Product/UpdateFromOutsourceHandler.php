<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\UpdateFromOutsource;
use App\Repository\ProductRepository;
use App\Service\Product\Update\OutsourceProductUpdateService;
use App\Service\Product\Update\PropertyUpdaters\SpecificationsUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateFromOutsourceHandler implements MessageHandlerInterface
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected OutsourceProductUpdateService $outsourceProductUpdateService,
        protected EntityManagerInterface $em
    ) {
    }

    public function __invoke(UpdateFromOutsource $message): void
    {
        $product = $this->productRepository->find($message->getProductId());

        $this->outsourceProductUpdateService->update(
            $product,
            new SpecificationsUpdater()
        );

        $this->em->flush();
    }
}
