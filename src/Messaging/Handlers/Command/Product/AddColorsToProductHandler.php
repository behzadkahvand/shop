<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Entity\Product;
use App\Entity\ProductOptionValue;
use App\Messaging\Messages\Command\Product\AddColorsToProduct;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddColorsToProductHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(AddColorsToProduct $addColorsToProduct): void
    {
        $productId = $addColorsToProduct->getProductId();

        /** @var Product $product */
        $product = $this->entityManager->getReference(Product::class, $productId);
        if (!$product) {
            $this->logger->error(sprintf('It can not add colors to product %d when product not exist!', $productId));
            return;
        }

        $colors = collect($product->getColorsOption())->map(
            function (ProductOptionValue $ov) {
                return [
                    "code"       => $ov->getCode(),
                    "value"      => $ov->getValue(),
                    "attributes" => $ov->getAttributes(),
                ];
            }
        )->toArray();
        if (!empty($colors)) {
            $product->setColors($colors);
            $this->entityManager->flush();
        }
    }
}
