<?php

namespace App\Serializer\Normalizer;

use App\Dictionary\OrderStatus;
use App\Entity\Order;
use App\Repository\OrderCancelReasonOrderRepository;
use App\Service\Utils\WebsiteAreaService;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class AdminOrderNormalizer extends AbstractCacheableNormalizer
{
    private ObjectNormalizer $normalizer;

    private WebsiteAreaService $areaService;

    private bool $isAdminArea;

    /**
     * @var OrderCancelReasonOrderRepository
     */
    private OrderCancelReasonOrderRepository $orderCancelReasonOrderRepository;

    public function __construct(
        WebsiteAreaService $areaService,
        ObjectNormalizer $normalizer,
        OrderCancelReasonOrderRepository $orderCancelReasonOrderRepository
    ) {
        $this->areaService                      = $areaService;
        $this->normalizer                       = $normalizer;
        $this->orderCancelReasonOrderRepository = $orderCancelReasonOrderRepository;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (array_key_exists('id', $normalizedData)) {
            $normalizedData['id'] = $object->getIdentifier();
        }

        if (array_key_exists('status', $normalizedData) && OrderStatus::CANCELED === $normalizedData['status']) {
            $normalizedData['cancelReason'] = $this->getCancelReason($object);
        }

        return $normalizedData;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        if (!isset($this->isAdminArea)) {
            $this->isAdminArea = $this->areaService->isAdminArea();
        }

        return $this->isAdminArea && $data instanceof Order;
    }

    /**
     * @param Order $order
     *
     * @return string|null
     */
    private function getCancelReason(Order $order): ?string
    {
        $orderCancelReasonOrder = $this->orderCancelReasonOrderRepository->findOneBy(compact('order'));

        return $orderCancelReasonOrder?->getCancelReason()?->getReason();
    }
}
