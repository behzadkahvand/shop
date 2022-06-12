<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Entity\Product;
use App\Messaging\Messages\Command\Product\IncreaseProductViewsCount;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class IncreaseProductViewsCountHandler implements MessageHandlerInterface
{
    public function __construct(protected EntityManagerInterface $em, protected ProductRepository $productRepository)
    {
    }

    public function __invoke(IncreaseProductViewsCount $message): void
    {
        /** @var Product $product */
        $product = $this->productRepository->find($message->getProductId());

        $product->incrementVisitCount();

        $this->em->flush();
    }
}
