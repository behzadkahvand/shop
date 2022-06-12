<?php

namespace App\Serializer\Normalizer;

use App\Dictionary\CustomerOrderShipmentStatus;
use App\Entity\OrderShipment;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomerOrderShipmentNormalizer extends AbstractCacheableNormalizer
{
    private WebsiteAreaService $areaService;

    private ObjectNormalizer $normalizer;

    private bool $isCustomerArea;

    public function __construct(
        WebsiteAreaService $areaService,
        ObjectNormalizer $normalizer
    ) {
        $this->areaService = $areaService;
        $this->normalizer = $normalizer;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (array_key_exists('status', $normalizedData)) {
            $normalizedData['status'] = CustomerOrderShipmentStatus::toArray()[$normalizedData['status']];
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

        return $this->isCustomerArea && $data instanceof OrderShipment;
    }
}
