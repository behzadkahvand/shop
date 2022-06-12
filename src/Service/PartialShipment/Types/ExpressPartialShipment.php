<?php

namespace App\Service\PartialShipment\Types;

use App\Service\PartialShipment\Exceptions\PartialShipmentCanNotBeFreezedException;
use App\Service\PartialShipment\Exceptions\PartialShipmentCanNotBeValidatedException;
use App\Service\PartialShipment\ValueObject\BaseFreezedPartialShipment;
use App\Service\PartialShipment\ValueObject\ExpressFreezedPartialShipment;
use App\Service\PartialShipment\ValueObject\ExpressPartialDeliveryDate;
use DateTimeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ExpressPartialShipment
 */
class ExpressPartialShipment extends AbstractPartialShipment implements ValidatablePartialShipmentInterface
{
    private bool $isValid = false;

    public function validate(
        DateTimeInterface $selectedDatetime,
        ValidatorInterface $validator
    ): ConstraintViolationListInterface {
        if (!isset($this->baseDeliveryDate)) {
            throw new PartialShipmentCanNotBeValidatedException();
        }

        $violations = $validator->validate($selectedDatetime, $this->getValidationConstraints());

        if (0 === count($violations)) {
            $this->isValid = true;

            return $violations;
        }

        $violations = iterator_to_array($violations);

        foreach ($violations as $i => $violation) {
            $violations[$i] = $this->prepareViolation($violation);
        }

        return new ConstraintViolationList($violations);
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function freeze(DateTimeInterface $selectedDatetime): BaseFreezedPartialShipment
    {
        if (0 === count($this->calculatedDeliveryDates)) {
            throw new PartialShipmentCanNotBeFreezedException(
                'Can not freeze partial shipment as delivery dates are not calculated yet!'
            );
        }

        $frozenPartialShipment = parent::freeze($selectedDatetime);

        $date           = $selectedDatetime->format('Y-m-d');
        $time           = $selectedDatetime->format('H:i');
        $shippingPeriod = collect($this->calculatedDeliveryDates)->map(
            function (ExpressPartialDeliveryDate $calculation) use ($date, $time) {
                if ($date !== $calculation->getDeliveryDate()->format('Y-m-d')) {
                    return false;
                }

                foreach ($calculation->getPeriods() as $period) {
                    if ($period->getStart()->format('H:i') === $time) {
                        return $period->getShippingPeriod();
                    }
                }

                return false;
            }
        )->filter()->first();

        return new ExpressFreezedPartialShipment(
            $frozenPartialShipment->getShipmentItems(),
            $frozenPartialShipment->getShippingMethod(),
            $frozenPartialShipment->getPrice(),
            $frozenPartialShipment->getDeliveryDate(),
            $frozenPartialShipment->getTitle(),
            $shippingPeriod
        );
    }

    protected function getValidationConstraints(): Constraint
    {
        $callback = function (?DateTimeInterface $payload, ExecutionContextInterface $context) {
            if (null === $payload) {
                $context->buildViolation('Delivery datetime is not selected.')->atPath('shipments')->addViolation();

                return;
            }

            /** @var ExpressPartialDeliveryDate $deliveryDate */
            foreach ($this->calculatedDeliveryDates as $deliveryDate) {
                if ($deliveryDate->validate($payload, $context)) {
                    return;
                }
            }

            $context->buildViolation('Selected date is not valid.')->atPath('deliveryDate')->addViolation();
        };

        return new Callback(compact('callback'));
    }

    private function prepareViolation(ConstraintViolationInterface $violation): ConstraintViolationInterface
    {
        $id = $this->getId();

        return new ConstraintViolation(
            $violation->getMessage(),
            $violation->getMessageTemplate(),
            $violation->getParameters(),
            $violation->getRoot(),
            sprintf('shipments.%s.%s', $id, $violation->getPropertyPath()),
            $violation->getInvalidValue(),
            $violation->getPlural(),
            $violation->getCode(),
            $violation->getConstraint()
        );
    }
}
