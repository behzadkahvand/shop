<?php

namespace App\Serializer\Normalizer;

use App\Entity\InventoryUpdateDemand;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class InventoryUpdateDemandNormalizer extends AbstractCacheableNormalizer
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

        $normalizedData['filePath'] = null;

        if ($object->getFileName() && $object->getDirPath()) {
            $normalizedData['filePath'] = '/' . $object->getDirPath() . '/' . $object->getFileName();
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

        return $this->isSellerArea && $data instanceof InventoryUpdateDemand;
    }
}
