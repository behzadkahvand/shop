<?php

namespace App\Messaging\Handlers\Command\Product;

use App\Messaging\Messages\Command\Product\DownloadProductImageFromDigikala;
use App\Repository\ProductRepository;
use App\Service\Product\Update\DownloadProductImageFromDigikalaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DownloadProductImageFromDigikalaHandler implements MessageHandlerInterface
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected DownloadProductImageFromDigikalaService $downloader,
        protected EntityManagerInterface $em,
    ) {
    }

    public function __invoke(DownloadProductImageFromDigikala $message): void
    {
        $product = $this->productRepository->find($message->getProductId());

        $shouldCoverWatermark = !$message->isWatermarkRemovedFromUrl();

        $this->downloader->download(
            $product,
            $message->getImageUrl(),
            $shouldCoverWatermark,
            $message->isFeatureImage(),
        );

        $this->em->flush();
    }
}
