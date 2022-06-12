<?php

namespace App\Service\PartialShipment\Factory\Pipeline\Stages\PartialShipment;

use App\Entity\Delivery;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigurePartialShipmentPayload;
use App\Service\PartialShipment\Types\PartialShipment;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\PayloadAwarePipelineStageInterface;
use DateTimeImmutable;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * Class CalculateDescriptionStage
 */
final class CalculateDescriptionStage implements PayloadAwarePipelineStageInterface
{
    private const DESCRIPTION_TEMPLATE = 'non_express_partial_shipment_description';

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * CalculateDescriptionStage constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param AbstractPipelinePayload $payload
     *
     * @return AbstractPipelinePayload
     */
    public function __invoke(AbstractPipelinePayload $payload)
    {
        /** @var PartialShipment $partialShipment */
        $partialShipment = $payload->getPartialShipment();

        Assert::isInstanceOf($partialShipment, PartialShipment::class);

        $delivery               = $partialShipment->getShippingCategory()->getDelivery();
        $startAndEnd            = $this->getStartAndEnd($partialShipment, $delivery);
        $description            = $this->translator->trans(
            self::DESCRIPTION_TEMPLATE,
            $startAndEnd,
            'shipment',
            'fa'
        );

        $partialShipment->setDeliveryRange([$startAndEnd['start'], $startAndEnd['end']]);
        $partialShipment->setDescription($description);

        return $payload;
    }

    /**
     * @return string
     */
    public static function getSupportedPayload(): string
    {
        return ConfigurePartialShipmentPayload::class;
    }

    /**
     * @return int
     */
    public static function getPriority(): int
    {
        return 100;
    }

    /**
     * @param PartialShipment $partialShipment
     * @param Delivery $max
     *
     * @return array
     */
    private function getStartAndEnd(PartialShipment $partialShipment, Delivery $max): array
    {
        $today            = new DateTimeImmutable('today');
        $baseDeliveryDate = $partialShipment->getBaseDeliveryDate()->setTime(0, 0, 0);
        $start            = $baseDeliveryDate->modify("{$max->getStart()} day")->diff($today)->days;
        $end              = $baseDeliveryDate->modify("{$max->getEnd()} day")->diff($today)->days;

        return compact('start', 'end');
    }
}
