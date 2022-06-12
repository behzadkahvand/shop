<?php

namespace App\Serializer\Normalizer;

use App\Entity\ProductAttribute;
use App\Service\ProductAttribute\ProductAttributeValueService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class ProductAttributeNormalizer extends AbstractCacheableNormalizer
{
    private ObjectNormalizer $normalizer;

    private ProductAttributeValueService $productAttributeValueService;

    public function __construct(
        ObjectNormalizer $normalizer,
        ProductAttributeValueService $productAttributeGetValueService
    ) {
        $this->normalizer                   = $normalizer;
        $this->productAttributeValueService = $productAttributeGetValueService;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return array_merge($this->normalizer->normalize($object, $format, $context), [
            'value' => $this->productAttributeValueService->getValue($object),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof ProductAttribute;
    }
}
