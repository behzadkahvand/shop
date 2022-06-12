<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Messaging\Messages\Command\Product\AddBuyBoxToProduct;
use App\Repository\InventoryRepository;
use App\Service\Product\BuyBox\BuyBoxValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AddBuyBoxToProductHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected InventoryRepository $inventoryRepository,
        protected BuyBoxValidatorService $buyBoxValidator,
        protected EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(AddBuyBoxToProduct $addBuyBoxToProduct): void
    {
        $productId = $addBuyBoxToProduct->getProductId();

        $product = $this->entityManager->getReference(Product::class, $productId);

        if (!$product) {
            $this->logger->error(sprintf('It can not add buy box to product %d when product not exist!', $productId));

            return;
        }

        $inventories = $this->inventoryRepository->getAvailableInventoriesByProductId($productId);

        $buyBox = collect($inventories)
            ->sort(fn(Inventory $x, Inventory $y) => $x->getFinalPrice() - $y->getFinalPrice())
            ->first();

        if (!$buyBox) {
            $this->logger->error(sprintf('It can not add buy box to product %d when buy box not exist!', $productId));

            return;
        }

        /**
         * @var Product $product
         */
        if ($this->buyBoxValidator->validate($product, $buyBox)) {
            $product->setBuyBox($buyBox);

            $this->entityManager->flush();
        }
    }
}
