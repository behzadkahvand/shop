<?php

namespace App\Service\PartialShipment\ValueObject;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class ExpressPartialDeliveryDate
 */
class ExpressPartialDeliveryDate implements \JsonSerializable
{
    /**
     * @var \DateTimeInterface
     *
     * @Groups({"Default", "default", "cart.shipments"})
     */
    private \DateTimeInterface $deliveryDate;

    /**
     * @var array|PartialShipmentPeriod[]
     *
     * @Groups({"Default", "default", "cart.shipments"})
     * @OA\Property(type="array", @OA\Items(ref=@Model(type=PartialShipmentPeriod::class, groups={"cart.shipments"})))
     */
    private array $periods;

    /**
     * ExpressPartialDeliveryDate constructor.
     *
     * @param \DateTimeInterface $deliveryDate
     * @param array|PartialShipmentPeriod[] $periods
     */
    public function __construct(\DateTimeInterface $deliveryDate, array $periods)
    {
        if (empty($periods)) {
            throw new \InvalidArgumentException(sprintf('Class %s expects at least one period.', static::class));
        }

        foreach ($periods as $period) {
            if (!$period instanceof PartialShipmentPeriod) {
                throw new \InvalidArgumentException(sprintf(
                    'Class %s expects an array of %s for periods. %s given.',
                    static::class,
                    PartialShipmentPeriod::class,
                    (is_object($period) ? 'Instance of ' : '') . get_debug_type($period)
                ));
            }
        }

        $this->deliveryDate = $deliveryDate;
        $this->periods = $periods;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDeliveryDate(): \DateTimeInterface
    {
        return $this->deliveryDate;
    }

    /**
     * @return array
     */
    public function getPeriods(): array
    {
        return $this->periods;
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @param ExecutionContextInterface $context
     *
     * @return bool
     */
    public function validate(\DateTimeInterface $dateTime, $context): bool
    {
        $calculatedDeliveryDate = $this->deliveryDate->format('Y-m-d');
        $selectedDeliveryDate   = $dateTime->format('Y-m-d');

        if ($calculatedDeliveryDate !== $selectedDeliveryDate) {
            return false;
        }

        $time = $dateTime->format('H:i');
        foreach ($this->periods as $period) {
            if ($period->startTimeEquals($time)) {
                if (!$period->isSelectable()) {
                    $context->buildViolation('Selected time is not valid.')->atPath('deliveryDate')->addViolation();
                }

                return $period->isSelectable();
            }
        }

        $context->buildViolation('Selected time is not valid.')->atPath('deliveryDate')->addViolation();

        return false;
    }

    /**
     * @param \DateTimeInterface $orderDatetime
     *
     * @return bool
     */
    public function isOpenForDelivery(\DateTimeInterface $orderDatetime): bool
    {
        foreach ($this->periods as $period) {
            if ($period->isOpenForDelivery($orderDatetime)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'date'    => $this->deliveryDate->format('Y-m-d'),
            'periods' => $this->periods,
        ];
    }
}
