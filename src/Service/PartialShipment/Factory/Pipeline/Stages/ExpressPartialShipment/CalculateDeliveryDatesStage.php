<?php

namespace App\Service\PartialShipment\Factory\Pipeline\Stages\ExpressPartialShipment;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\ShippingPeriod;
use App\Repository\ShippingPeriodRepository;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Calculators\Express\ExpressShipmentDeliveryDateAndPeriodCalculatorFactory;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\ValueObject\ExpressPartialDeliveryDate;
use App\Service\PartialShipment\ValueObject\PartialShipmentPeriod;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\PayloadAwarePipelineStageInterface;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Class CalculateDeliveryDatesStage
 */
final class CalculateDeliveryDatesStage implements PayloadAwarePipelineStageInterface
{
    private const PARTIAL_SHIPMENT_SELECTABLE_DAYS_COUNT = 4;

    /**
     * @var HolidayServiceInterface
     */
    private HolidayServiceInterface $holidayService;

    /**
     * @var ShippingPeriodRepository
     */
    private ShippingPeriodRepository $shippingPeriodRepository;

    /**
     * @var array|ShippingPeriod[]
     */
    private array $periods;

    /**
     * @var ConfigurationServiceInterface
     */
    private ConfigurationServiceInterface $configurationService;

    /**
     * @var ExpressShipmentDeliveryDateAndPeriodCalculatorFactory
     */
    private ExpressShipmentDeliveryDateAndPeriodCalculatorFactory $factory;

    /**
     * CalculateDeliveryDatesStage constructor.
     *
     * @param HolidayServiceInterface $holidayService
     * @param ShippingPeriodRepository $shippingPeriodRepository
     * @param ConfigurationServiceInterface $configurationService
     * @param ExpressShipmentDeliveryDateAndPeriodCalculatorFactory $shipmentDeliveryDateAndPeriodCalculatorFactory
     */
    public function __construct(
        HolidayServiceInterface $holidayService,
        ShippingPeriodRepository $shippingPeriodRepository,
        ConfigurationServiceInterface $configurationService,
        ExpressShipmentDeliveryDateAndPeriodCalculatorFactory $shipmentDeliveryDateAndPeriodCalculatorFactory
    ) {
        $this->holidayService           = $holidayService;
        $this->shippingPeriodRepository = $shippingPeriodRepository;
        $this->configurationService     = $configurationService;
        $this->factory                  = $shipmentDeliveryDateAndPeriodCalculatorFactory;
    }

    /**
     * @param AbstractPipelinePayload $payload
     *
     * @return AbstractPipelinePayload
     */
    public function __invoke(AbstractPipelinePayload $payload)
    {
        /** @var AbstractPartialShipment $partialShipment */
        $partialShipment            = $payload->getPartialShipment();
        $periods                    = $this->getPeriods();
        $result                     = $this->factory->create($payload)->calculate($payload, $periods);
        $calculatedBaseDeliveryDate = $result->getBaseDeliveryDate();

        $calculatedDeliveryDates = [
            new ExpressPartialDeliveryDate($calculatedBaseDeliveryDate, $result->getPeriods()),
        ];

        $currentDeliveryDate = $calculatedBaseDeliveryDate;
        for ($i = 1, $j = $this->getSelectableDaysCount(); $i < $j; $i++) {
            $currentDeliveryDate       = $this->getFirstOpenShipmentDateSince($currentDeliveryDate->modify('1 day'));
            $calculatedDeliveryDates[] = new ExpressPartialDeliveryDate($currentDeliveryDate, $periods);
        }

        $partialShipment->setCalculatedDeliveryDates($calculatedDeliveryDates);

        $partialShipmentDeliveryDate = $partialShipment->getBaseDeliveryDate();

        if ($this->baseDeliveryDateIsChanged($partialShipmentDeliveryDate, $calculatedBaseDeliveryDate)) {
            $partialShipment->setBaseDeliveryDate($calculatedBaseDeliveryDate);
        }

        return $payload;
    }

    /**
     * @return string
     */
    public static function getSupportedPayload(): string
    {
        return ConfigureExpressPartialShipmentPayload::class;
    }

    /**
     * @return int
     */
    public static function getPriority(): int
    {
        return 100;
    }

    /**
     * @return PartialShipmentPeriod[]|array
     */
    public function getPeriods(): array
    {
        if (!isset($this->periods)) {
            $this->periods = array_map(
                [PartialShipmentPeriod::class, 'fromShippingPeriod'],
                $this->shippingPeriodRepository->findBy(['isActive' => true], ['start' => 'ASC'])
            );
        }

        return $this->periods;
    }

    /**
     * @return int
     */
    private function getSelectableDaysCount(): int
    {
        $daysCount = $this->getConfig(ConfigurationCodeDictionary::PARTIAL_SHIPMENT_SELECTABLE_DAYS_COUNT);

        return is_numeric($daysCount) ? (int) $daysCount : self::PARTIAL_SHIPMENT_SELECTABLE_DAYS_COUNT;
    }

    /**
     * @param DateTimeImmutable $dateTime
     *
     * @return DateTimeInterface
     */
    private function getFirstOpenShipmentDateSince(DateTimeImmutable $dateTime): DateTimeInterface
    {
        return $this->holidayService->getFirstOpenShipmentDateSince($dateTime);
    }

    /**
     * @param string $code
     *
     * @return int|string|null
     */
    private function getConfig(string $code)
    {
        $configs = $this->configurationService->findByCode($code);

        return $configs ? $configs->getValue() : null;
    }

    /**
     * @param DateTimeImmutable $partialShipmentDeliveryDate
     * @param DateTimeImmutable $baseDeliveryDate
     *
     * @return bool
     */
    private function baseDeliveryDateIsChanged(
        DateTimeImmutable $partialShipmentDeliveryDate,
        DateTimeImmutable $baseDeliveryDate
    ): bool {
        return $partialShipmentDeliveryDate->format('Y-m-d') !== $baseDeliveryDate->format('Y-m-d');
    }
}
