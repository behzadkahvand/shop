<?php

namespace App\Messaging\Messages\Command\Product;

class DownloadProductImageFromDigikala
{
    public function __construct(
        private int $productId,
        private string $imageUrl,
        private bool $isFeatureImage,
        private bool $isWatermarkRemovedFromUrl
    ) {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function isFeatureImage(): bool
    {
        return $this->isFeatureImage;
    }

    public function isWatermarkRemovedFromUrl(): bool
    {
        return $this->isWatermarkRemovedFromUrl;
    }
}
