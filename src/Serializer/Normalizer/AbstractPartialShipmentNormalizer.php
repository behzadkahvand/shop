<?php

namespace App\Serializer\Normalizer;

use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class AbstractPartialShipmentNormalizer
 */
final class AbstractPartialShipmentNormalizer extends AbstractCacheableNormalizer
{
    /**
     * @var ObjectNormalizer
     */
    private ObjectNormalizer $normalizer;

    /**
     * AbstractPartialShipmentNormalizer constructor.
     *
     * @param ObjectNormalizer $normalizer
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
        /** @var AbstractPartialShipment $object */
        return [
            'id'               => $object->getId(),
            'zoneName'         => $object->getZone()->getName(),
            'shippingMethod'   => $object->getShippingMethod()->getName(),
            'shippingCategory' => $object->getShippingCategory()->getTitle(),
            'price'            => $object->getPrice(),
            'items'            => $this->normalizeShipmentItems($object->getShipmentItems(), $format, $context),
            'description'      => $object->getDescription(),
            'deliveryDates'    => $object->getCalculatedDeliveryDates(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof AbstractPartialShipment;
    }

    /**
     * @param array|PartialShipmentItem[] $shipmentItems
     * @param string|null $format
     * @param array $context
     *
     * @return array|PartialShipmentItem
     */
    private function normalizeShipmentItems(array $shipmentItems, ?string $format, array $context): array
    {
        return array_map(function (PartialShipmentItem $item) use ($format, $context) {
            $item              = $item->jsonSerialize();
            $item['inventory'] = $this->normalizer->normalize($item['inventory'], $format, $context);

            return $item;
        }, $shipmentItems);
    }
}
