<?php

namespace App\Serializer\Normalizer;

use App\Dictionary\SellerOrderStatus;
use App\Entity\Order;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SellerOrderNormalizer extends AbstractCacheableNormalizer
{
    private WebsiteAreaService $areaService;

    private ObjectNormalizer $normalizer;

    private bool $isSellerArea;

    public function __construct(
        WebsiteAreaService $areaService,
        ObjectNormalizer $normalizer
    ) {
        $this->normalizer   = $normalizer;
        $this->areaService = $areaService;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (array_key_exists('status', $normalizedData)) {
            $normalizedData['status'] = SellerOrderStatus::toArray()[$normalizedData['status']];
        }

        if (array_key_exists('id', $normalizedData)) {
            $normalizedData['id'] = $object->getIdentifier();
        }

        return $normalizedData;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        if (!isset($this->isSellerArea)) {
            $this->isSellerArea = $this->areaService->isSellerArea();
        }

        return $this->isSellerArea && $data instanceof Order;
    }
}
