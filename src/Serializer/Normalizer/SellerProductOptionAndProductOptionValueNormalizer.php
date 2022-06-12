<?php

namespace App\Serializer\Normalizer;

use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Service\Utils\WebsiteAreaService;

/**
 * Class SellerProductOptionAndProductOptionValueNormalizer
 */
final class SellerProductOptionAndProductOptionValueNormalizer extends AbstractCacheableNormalizer
{
    /**
     * @var bool
     */
    private bool $isSellerArea;

    /**
     * @var WebsiteAreaService
     */
    private WebsiteAreaService $websiteAreaService;

    /**
     * SellerProductOptionAndProductOptionValueNormalizer constructor.
     *
     * @param WebsiteAreaService $websiteAreaService
     */
    public function __construct(WebsiteAreaService $websiteAreaService)
    {
        $this->websiteAreaService = $websiteAreaService;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($object instanceof ProductOption) {
            return [
                'id'     => $object->getId(),
                'name'   => $object->getName(),
                'code'   => $object->getCode(),
                'values' => $object->getValues()->map(function (ProductOptionValue $value) {
                    return [
                        'id'         => $value->getId(),
                        'code'       => $value->getCode(),
                        'value'      => $value->getValue(),
                        'attributes' => $value->getAttributes(),
                    ];
                })->toArray(),
            ];
        }

        $option = $object->getOption();

        return [
            'id'         => $object->getId(),
            'code'       => $object->getCode(),
            'value'      => $object->getValue(),
            'attributes' => $object->getAttributes(),
            'option'     => [
                'id'     => $option->getId(),
                'name'   => $option->getName(),
                'code'   => $option->getCode(),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $this->isSellerArea()
            && is_object($data)
            && in_array(get_class($data), [ProductOption::class, ProductOptionValue::class]);
    }

    /**
     * @return bool
     */
    public function isSellerArea(): bool
    {
        if (!isset($this->isSellerArea)) {
            $this->isSellerArea = $this->websiteAreaService->isSellerArea();
        }

        return $this->isSellerArea;
    }
}
