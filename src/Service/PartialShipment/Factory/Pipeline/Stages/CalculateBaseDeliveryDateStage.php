<?php

namespace App\Service\PartialShipment\Factory\Pipeline\Stages;

use App\Entity\Seller;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Pipeline\Payload\CreatePartialShipmentPayload;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\PayloadAwarePipelineStageInterface;
use DateTimeImmutable;

/**
 * Class CalculateBaseDeliveryDateStage
 */
final class CalculateBaseDeliveryDateStage implements PayloadAwarePipelineStageInterface
{
    /**
     * @var HolidayServiceInterface
     */
    private HolidayServiceInterface $holidayService;

    /**
     * CalculateBaseDeliveryDateStage constructor.
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
        /** @var AbstractPartialShipment $partialShipment */
        $partialShipment  = $payload->getPartialShipment();
        $orderDatetime    = $payload->getOrderDatetime();
        $sellers          = $partialShipment->getSellers();
        $shipmentDateTime = $this->holidayService->getFirstOpenSupplyDateSince($orderDatetime, ...$sellers);
        $baseDeliveryDate = $this->applyHolidays(
            $partialShipment->getItemsMaxSuppliesIn(),
            $shipmentDateTime,
            ...$sellers
        );

        $partialShipment->setBaseDeliveryDate($baseDeliveryDate);

        return $payload;
    }

    /**
     * @return string
     */
    public static function getSupportedPayload(): string
    {
        return CreatePartialShipmentPayload::class;
    }

    /**
     * @return int
     */
    public static function getPriority(): int
    {
        return 100;
    }

    /**
     * @param int $suppliesIn
     * @param DateTimeImmutable $baseShipmentDateTime
     * @param Seller ...$sellers
     *
     * @return DateTimeImmutable|false
     */
    private function applyHolidays(int $suppliesIn, DateTimeImmutable $baseShipmentDateTime, Seller ...$sellers)
    {
        $dt = $baseShipmentDateTime;

        while (0 < $suppliesIn) {
            $dt = $dt->modify('1 day');

            if (
                $this->holidayService->isOpenForSupply($dt) &&
                $this->holidayService->isOpenForSupply($dt, ...$sellers)
            ) {
                $suppliesIn--;
            }
        }

        $dt = $this->holidayService->getFirstOpenShipmentDateSince($dt, ...$sellers);

        return to_date_time_immutable($dt);
    }
}
