<?php

namespace App\Serializer\Normalizer;

use App\Entity\OrderItem;
use App\Repository\SellerOrderItemRepository;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class AdminOrderItemNormalizer extends AbstractCacheableNormalizer
{
    private ObjectNormalizer $normalizer;

    private WebsiteAreaService $areaService;

    private SellerOrderItemRepository $sellerOrderItemRepository;

    private bool $isAdminArea;

    public function __construct(
        WebsiteAreaService $areaService,
        ObjectNormalizer $normalizer
    ) {
        $this->areaService = $areaService;
        $this->normalizer  = $normalizer;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return array_merge($this->normalizer->normalize($object, $format, $context), [
            'sent' => $object->isSent(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        if (!isset($this->isAdminArea)) {
            $this->isAdminArea = $this->areaService->isAdminArea();
        }

        return $this->isAdminArea && $data instanceof OrderItem;
    }
}
