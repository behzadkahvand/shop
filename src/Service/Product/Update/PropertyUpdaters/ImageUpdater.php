<?php

namespace App\Service\Product\Update\PropertyUpdaters;

use App\Entity\Product;
use App\Exceptions\Entity\NotPersistedEntityException;
use App\Messaging\Messages\Command\Product\DownloadProductImageFromDigikala;
use App\Service\Product\Update\PropertyUpdater;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class ImageUpdater implements PropertyUpdater
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    /**
     * @throws NotPersistedEntityException
     */
    public function update(Product $product, array $dkp): void
    {
        if (!$this->isPersisted($product)) {
            throw new NotPersistedEntityException(
                'ImageUpdater only works for persisted products.'
            );
        }

        $featureImage = $this->fetchFeatureImageLinkFrom($dkp);
        $this->formatLinkAndDispatchDownloadMessage($product, $featureImage, true);

        $galleryImages = $this->fetchGalleryImagesLinkFrom($dkp);
        foreach ($galleryImages as $imageUrl) {
            $this->formatLinkAndDispatchDownloadMessage($product, $imageUrl, false);
        }
    }

    private function fetchFeatureImageLinkFrom(array $dkp): string
    {
        return $dkp['product']['images']['main']['url'][0];
    }

    private function fetchGalleryImagesLinkFrom(array $dkp): array
    {
        return array_map(
            fn(array $galleryImage): string => $galleryImage['url'][0],
            $dkp['product']['images']['list']
        );
    }

    private function formatLinkAndDispatchDownloadMessage(Product $product, string $url, bool $isFeatureImage): void
    {
        $isWatermarkRemovedFromUrl = $isFeatureImage || $this->hasWatermark($url);
        $imageUrl = $this->formatAndResize($url);

        $message = new DownloadProductImageFromDigikala(
            $product->getId(),
            $imageUrl,
            $isFeatureImage,
            $isWatermarkRemovedFromUrl
        );

        $this->bus->dispatch(
            new Envelope(
                $message,
                [new DelayStamp(rand(1000, 150000))]
            )
        );
    }

    private function formatAndResize(string $imageUrl): string
    {
        return strtok($imageUrl, '?') . '?x-oss-process=image/resize,m_lfit,h_1200,w_1200/quality,q_80';
    }

    private function hasWatermark(string $imageUrl): bool
    {
        return str_contains($imageUrl, '/watermark,');
    }

    private function isPersisted(Product $product): bool
    {
        // Product should exist in DB, but we ignored that checkup due to reducing db calls
        return null !== $product->getId();
    }
}
