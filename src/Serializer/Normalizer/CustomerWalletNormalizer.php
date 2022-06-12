<?php

namespace App\Serializer\Normalizer;

use App\Entity\Wallet;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomerWalletNormalizer extends AbstractCacheableNormalizer
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

        if ($normalizedData['isFrozen']) {
            $normalizedData['balance'] = 0;
        }

        unset($normalizedData['isFrozen']);

        return $normalizedData;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        if (!isset($this->isCustomerArea)) {
            $this->isCustomerArea = $this->areaService->isCustomerArea();
        }

        return $this->isCustomerArea && $data instanceof Wallet;
    }
}
