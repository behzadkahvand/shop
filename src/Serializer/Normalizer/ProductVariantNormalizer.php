<?php

namespace App\Serializer\Normalizer;

use App\Entity\ProductOptionValue;
use App\Entity\ProductVariant;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class ProductVariantNormalizer
 */
final class ProductVariantNormalizer extends AbstractCacheableNormalizer
{
    private ObjectNormalizer $normalizer;

    /**
     * ProductVariantNormalizer constructor.
     */
    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var ProductVariant $object */
        $normalized = $this->normalizer->normalize($object, $format, $context);

        return array_merge($normalized, [
            'options' => [
                'color'        => $this->extractOptionValues($object->getColor()),
                'guarantee'    => $this->extractOptionValues($object->getGuaranty()),
                'otherOptions' => $this->extractOptionValues($object->getOtherOption()),
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof ProductVariant;
    }

    /**
     * @param ProductOptionValue|null $productOptionValue
     *
     * @return array|null
     */
    private function extractOptionValues(?ProductOptionValue $productOptionValue): ?array
    {
        if (!$productOptionValue) {
            return null;
        }

        return [
            'code'   => $productOptionValue->getCode(),
            'value'  => $productOptionValue->getValue(),
            'attributes' => $productOptionValue->getAttributes(),
            'option' => [
                'name' => $productOptionValue->getOption()->getName(),
            ],
        ];
    }
}
