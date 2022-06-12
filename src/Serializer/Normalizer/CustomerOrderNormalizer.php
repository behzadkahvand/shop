<?php

namespace App\Serializer\Normalizer;

use App\Dictionary\CustomerOrderStatus;
use App\Entity\Order;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomerOrderNormalizer extends AbstractCacheableNormalizer
{
    private ObjectNormalizer $normalizer;

    private WebsiteAreaService $areaService;

    private bool $isCustomerArea;

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

        if (array_key_exists('status', $normalizedData)) {
            $normalizedData['status'] = CustomerOrderStatus::toArray()[$normalizedData['status']];
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
        if (!isset($this->isCustomerArea)) {
            $this->isCustomerArea = $this->areaService->isCustomerArea();
        }

        return $this->isCustomerArea && $data instanceof Order;
    }
}
