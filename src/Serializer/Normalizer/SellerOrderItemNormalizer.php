<?php

namespace App\Serializer\Normalizer;

use App\Dictionary\SellerOrderItemStatusMappingDictionary;
use App\Entity\SellerOrderItem;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class SellerOrderItemNormalizer
 */
final class SellerOrderItemNormalizer extends AbstractCacheableNormalizer
{
    private WebsiteAreaService $areaService;

    private ObjectNormalizer $normalizer;

    private bool $isSellerArea;

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
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (isset($normalizedData['status'])) {
            $mapping                  = SellerOrderItemStatusMappingDictionary::toArray();
            $normalizedData['status'] = $mapping[$normalizedData['status']];
        }

        return$normalizedData;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        if (!isset($this->isSellerArea)) {
            $this->isSellerArea = $this->areaService->isSellerArea();
        }

        return $this->isSellerArea && $data instanceof SellerOrderItem;
    }
}
