<?php

namespace App\Service\PartialShipment\Factory\Pipeline\Stages\PartialShipment;

use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigurePartialShipmentPayload;
use App\Service\PartialShipment\Types\PartialShipment;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\PayloadAwarePipelineStageInterface;
use DateTimeImmutable;
use Webmozart\Assert\Assert;

/**
 * Class CalculateDeliveryDatesStage
 */
final class CalculateDeliveryDatesStage implements PayloadAwarePipelineStageInterface
{
    private HolidayServiceInterface $holidayService;

    /**
     * CalculateDeliveryDatesStage constructor.
     *
     * @param HolidayServiceInterface $holidayService
     */
    public function __construct(HolidayServiceInterface $holidayService)
    {
        $this->holidayService = $holidayService;
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

        $baseDeliveryDate = $this->applyWarehouseProcessingDuration($partialShipment->getBaseDeliveryDate());

        $partialShipment->setBaseDeliveryDate(
            $this->holidayService->getFirstOpenShipmentDateSince($baseDeliveryDate)
        );

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
        return 110;
    }

    /**
     * @param DateTimeImmutable $baseDeliveryDate
     *
     * @return DateTimeImmutable
     */
    private function applyWarehouseProcessingDuration(DateTimeImmutable $baseDeliveryDate): DateTimeImmutable
    {
        return $baseDeliveryDate->modify('1 day');
    }
}
